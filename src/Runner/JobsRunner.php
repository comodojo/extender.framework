<?php namespace Comodojo\Extender\Runner;

use \Exception;
use \Comodojo\Extender\Checks;
use \Comodojo\Extender\Queue;

/**
 * Jobs runner
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class JobsRunner {

    /**
     * Jobs database (a simple array!).
     *
     * @var     array
     */
    private $jobs = array();

    /**
     * Logger instance
     *
     * @var     Object
     */
    private $logger = null;

    /**
     * Multithread switch
     *
     * @var     bool
     */
    private $multithread = false;

    /**
     * Array of completed processes
     *
     * @var     array
     */
    private $completed_processes = array();

    /**
     * Array of running processes
     *
     * @var     array
     */
    private $running_processes = array();

    /**
     * Array of forked processes
     *
     * @var     array
     */
    private $forked_processes = array();

    /**
     * Amount of data (bits) to read from interprocess sockets
     *
     * @var     int
     */
    private $max_result_bytes_in_multithread = null;

    /**
     * Maximum child runtime (after this interval parent process will start to kill)
     *
     * @var     array
     */
    private $max_childs_runtime = null;

    /**
     * Array of ipc inter-processes sockets
     *
     * @var     array
     */
    private $ipc_array = array();
    
    /**
     * Number of processes waiting in the queue
     *
     * @var     int
     */
    private $queued_processes = 0;

    /**
     * Array of chunks from current jobs
     * 
     * It is used to divide jobs in groups and generate the queue
     *
     * @var     array
     */
    private $queued_chunks = array();

    /**
     * Time between SIGTERM and SIGKILL when child is killed
     * 
     * Just to give it a chance
     *
     * @var     int
     */
    static private $lagger_timeout = 5;

    /**
     * Runner constructor
     *
     * @param   Comodojo\Extender\Debug   $logger                             Logger instance
     * @param   bool                      $multithread                        Enable/disable multithread mode
     * @param   int                       $max_result_bytes_in_multithread    Max result bytes
     * @param   int                       $max_childs_runtime                 Max child runtime
     */
    final public function __construct($logger, $multithread, $max_result_bytes_in_multithread, $max_childs_runtime) {

        $this->logger = $logger;

        $this->multithread = $multithread;

        $this->max_result_bytes_in_multithread = $max_result_bytes_in_multithread;

        $this->max_childs_runtime = $max_childs_runtime;

    }

    /**
     * Add job to current queue
     *
     * @param   Comodojo\Extender\Job\Job  $job    An instance of \Comodojo\Extender\Job\Job
     *
     * @return  string  A job unique identifier
     */
    final public function addJob(\Comodojo\Extender\Job\Job $job) {

        $uid = self::getJobUid();

        try {

            $class = $job->getClass();

            if ( class_exists($class) === false ) throw new Exception("Task cannot be loaded");

            $this->jobs[$uid] = array(
                "name"      =>  $job->getName(),
                "id"        =>  $job->getId(),
                "parameters"=>  $job->getParameters(),
                "task"      =>  $job->getTask(),
                "class"     =>  $class
            );

        } catch (Exception $e) {

            $this->logger->error('Error including job',array(
                "JOBUID"=> $uid,
                "ERROR" => $e->getMessage(),
                "ERRID" => $e->getCode()
            ));

            return false;

        }

        return $uid;

    }

    /**
     * Free (reset) runner instance
     *
     */
    final public function free() {

        $this->jobs = array();
        $this->completed_processes = array();
        $this->running_processes = array();
        $this->forked_processes = array();
        $this->ipc_array = array();

    }

    /**
     * Execute job(s) in current queue
     *
     * @return  array   An array of completed processes
     */
    final public function run() {

        // if jobs > concurrent jobs, create the queue

        if ( $this->multithread AND defined("EXTENDER_MAX_CHILDS") AND sizeof($this->jobs) > EXTENDER_MAX_CHILDS AND EXTENDER_MAX_CHILDS != 0 ) {

            $this->queued_processes = sizeof($this->jobs);

            // split jobs in chunks

            $this->queued_chunks = array_chunk($this->jobs, EXTENDER_MAX_CHILDS, true);

            // exec chunks, one at time

            foreach ($this->queued_chunks as $chunk) {
                
                $this->queued_processes = $this->queued_processes - sizeof($chunk);

                Queue::dump(sizeof($chunk), $this->queued_processes);

                $this->forker($chunk);

                if ( $this->multithread ) $this->logger->info("Extender forked ".sizeof($this->forked_processes)." process(es) in the running queue", $this->forked_processes);

                $this->catcher();

                $this->forked_processes = array();

            }

        } else {

            Queue::dump(sizeof($this->jobs), 0);

            $this->forker($this->jobs);

            if ( $this->multithread ) $this->logger->info("Extender forked ".sizeof($this->forked_processes)." process(es) in the running queue", $this->forked_processes);

            $this->catcher();

        }

        // Dump the end queue status

        Queue::dump(sizeof($this->running_processes), $this->queued_processes);

        return $this->completed_processes;

    }

    /**
     * Terminate all running processes
     *
     * @param   int     Parent process pid
     */
    final public function killAll($parent_pid) {

        foreach ($this->running_processes as $pid => $process) {

            // if ( $pid !== $parent_pid) posix_kill($pid, SIGTERM);
            if ( $pid !== $parent_pid) self::kill($pid);

        }

    }

    /**
     * Fork or exec some jobs
     *
     * @param   array   $jobs   A subset of $this->jobs to process in a round
     */
    private function forker($jobs) {

        foreach ($jobs as $jobUid => $job) {
            
            if ( $this->multithread AND sizeof($jobs) > 1 ) {

                $status = $this->runMultithread($jobUid);

                if ( !is_null($status["pid"]) ) {

                    $this->running_processes[$status["pid"]] = array($status["name"], $status["uid"], $status["timestamp"], $status["id"]);

                    array_push($this->forked_processes, $status["pid"]);

                }

            } else {

                $status = $this->runSinglethread($jobUid);

                array_push($this->completed_processes, $status);

            }

        }

    }

    /**
     * Catch results from completed jobs
     * 
     */
    private function catcher() {

        $exec_time = microtime(true);

        while( !empty($this->running_processes) ) {

            foreach($this->running_processes as $pid => $job) {

                //$job[0] is name
                //$job[1] is uid
                //$job[2] is start timestamp
                //$job[3] is job id

                if( !self::isRunning($pid) ) {

                    list($reader,$writer) = $this->ipc_array[$job[1]];

                    socket_close($writer);
                    
                    $parent_result = socket_read($reader, $this->max_result_bytes_in_multithread, PHP_BINARY_READ);

                    if ( $parent_result === false ) {

                        $this->logger->error("socket_read() failed. Reason: ".socket_strerror(socket_last_error($reader)));

                        array_push($this->completed_processes,Array(
                            null,
                            $job[0],//$job_name,
                            false,
                            $job[2],//$start_timestamp,
                            null,
                            "socket_read() failed. Reason: ".socket_strerror(socket_last_error($reader)),
                            $job[3]
                        ));

                        $status = 'ERROR';

                    } else {

                        $result = unserialize(rtrim($parent_result));

                        socket_close($reader);
                        
                        array_push($this->completed_processes,Array(
                            $pid,
                            $job[0],//$job_name,
                            $result["success"],
                            $job[2],//$start_timestamp,
                            $result["timestamp"],
                            $result["result"],
                            $job[3]
                        ));

                        $status = $result["success"] ? 'SUCCESS' : 'ERROR';

                    }
                    
                    unset($this->running_processes[$pid]);

                    $this->logger->info("Removed pid ".$pid." from the running queue, job terminated with ".$status);

                } else {

                    $current_time = microtime(true);

                    if ($current_time > $exec_time + $this->max_childs_runtime) {

                        $this->logger->warning("Killing pid ".$pid." due to maximum exec time reached (>".$this->max_childs_runtime.")", array(
                            "START_TIME"    => $exec_time,
                            "CURRENT_TIME"  => $current_time,
                            "MAX_RUNTIME"   => $this->max_childs_runtime
                        ));

                        $kill = self::kill($pid);

                        if ( $kill ) $this->logger->warning("Pid ".$pid." killed");

                        else $this->logger->warning("Pid ".$pid." could not be killed");

                        list($reader,$writer) = $this->ipc_array[$job[1]];

                        socket_close($writer);
                        socket_close($reader);
                        
                        array_push($this->completed_processes,Array(
                            $pid,
                            $job[0],//$job_name,
                            false,
                            $job[2],//$start_timestamp,
                            $current_time,
                            "Job ".$job[0]." killed due to maximum exec time reached (>".$this->max_childs_runtime.")",
                            $job[3]
                        ));

                        unset($this->running_processes[$pid]);

                    }

                }

            }

        }

    }

    /**
     * Run job in singlethread mode
     *
     * @param   string  Job unique identifier
     *
     * @return  array   {[pid],[name],[success],[start],[end],[result],[id]}
     */
    private function runSinglethread($jobUid) {

        $job = $this->jobs[$jobUid];

        // get job start timestamp
        $start_timestamp = microtime(true);

        $name = $job['name'];

        $id = $job['id'];

        $parameters = $job['parameters'];

        $task = $job['task'];

        $task_class = $job['class'];

        try {

            // create a task instance

            $thetask = new $task_class($parameters, null, $name, $start_timestamp, false);

            // get the task pid (we are in singlethread mode)

            $pid = $thetask->getPid();

            // run task

            $result = $thetask->start();
        
        }
        catch (Exception $e) {
        
            return array($pid, $name, false, $start_timestamp, null, $e->getMessage(), $id);
        
        }

        return array($pid, $name, $result["success"], $start_timestamp, $result["timestamp"], $result["result"], $id);

    }

    /**
     * Run job in singlethread mode
     *
     * @param   string  Job unique identifier
     *
     * @return  array   {[pid],[name],[success],[start],[end],[result],[id]}
     */
    private function runMultithread($jobUid) {

        $job = $this->jobs[$jobUid];

        // get job start timestamp
        $start_timestamp = microtime(true);

        $name = $job['name'];

        $id = $job['id'];

        $parameters = $job['parameters'];

        $task = $job['task'];

        $task_class = $job['class'];

        $this->ipc_array[$jobUid] = array();

        // create a comm socket
        $socket = socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $this->ipc_array[$jobUid]);

        if ( $socket === false ) {

            $this->logger->error("No IPC communication, aborting", array(
                "JOBUID"=> $jobUid,
                "ERROR" => socket_strerror(socket_last_error()),
                "ERRID" => null
            ));

            array_push($this->completed_processes, array(
                null,
                $name,
                false,
                $start_timestamp,
                microtime(true),
                'No IPC communication, exiting - '.socket_strerror(socket_last_error()),
                $id
            ));

            return array(
                "pid"       =>  null,
                "name"      =>  $name,
                "uid"       =>  $jobUid,
                "timestamp" =>  $start_timestamp,
                "id"        =>  $id
            );

        }

        list($reader,$writer) = $this->ipc_array[$jobUid];

        $pid = pcntl_fork();

        if( $pid == -1 ) {

            $this->logger->error("Could not fok job, aborting");

            array_push($this->completed_processes,Array(
                null,
                $name,
                false,
                $start_timestamp,
                microtime(true),
                'Could not fok job',
                $id
            ));

        } elseif ($pid) {

            //PARENT will take actions on processes later

            self::adjustNiceness($pid, $this->logger);

        } else {
            
            socket_close($reader);

            $thetask = new $task_class($parameters, null, $name, $start_timestamp, true);

            try{

                $result = $thetask->start();

                $return = serialize(array(
                    "success"   =>  $result["success"],
                    "result"    =>  $result["result"],
                    "timestamp" =>  $result["timestamp"]
                ));

            }
            catch (Exception $e) {

                $return = serialize(Array(
                    "success"   =>  false,
                    "result"    =>  $e->getMessage(),
                    "timestamp" =>  microtime(true)
                ));
                
                if ( socket_write($writer, $return, strlen($return)) === false ) {

                    $this->logger->error("socket_write() failed ", array(
                        "ERROR" => socket_strerror(socket_last_error($writer))
                    ));

                }

                socket_close($writer);
                
                exit(1);

            }

            if ( socket_write($writer, $return, strlen($return)) === false ) {

                $this->logger->error("socket_write() failed ", array(
                    "ERROR" => socket_strerror(socket_last_error($writer))
                ));

            }

            socket_close($writer);

            exit(0);

        }

        return array(
            "pid"       =>  $pid == -1 ? null : $pid,
            "name"      =>  $name,
            "uid"       =>  $jobUid,
            "id"        =>  $id,
            "timestamp" =>  $start_timestamp
        );

    }

    /**
     * Return true if process is still running, false otherwise
     * 
     * @return  bool
     */
    static private function isRunning($pid) {

        return (pcntl_waitpid($pid, $status, WNOHANG) === 0);

    }

    /**
     * Kill a child process
     * 
     * @return  bool
     */
    static private function kill($pid) {

        $kill_time = time() + self::$lagger_timeout;

        $term = posix_kill($pid, SIGTERM);

        while ( time() < $kill_time ) {
            
            if ( !self::isRunning($pid) ) return $term;

        }

        return posix_kill($pid, SIGKILL);

    }

    /**
     * Get a job unique identifier
     * 
     * @return  string
     */
    static private function getJobUid() {

        return md5(uniqid(rand(), true), 0);

    }

    /**
     * Change child process priority according to EXTENDER_NICENESS
     *
     */
    static private function adjustNiceness($pid, $logger) {

        if ( Checks::multithread() AND defined("EXTENDER_CHILD_NICENESS") ) {

            $niceness = pcntl_setpriority($pid, EXTENDER_CHILD_NICENESS);

            if ( $niceness == false ) $logger->warning("Unable to set child process ".$pid." niceness to ".EXTENDER_CHILD_NICENESS);

        }

    }

}
