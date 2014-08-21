<?php namespace Comodojo\Extender\Runner;

use \Exception;

/**
 * Jobs runner
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
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

    private $jobs = array();

    private $logger = null;

    private $multithread = false;

    private $completed_processes = array();

    private $running_processes = array();

    private $forked_processes = array();

    private $max_result_bytes_in_multithread = null;

    private $max_childs_runtime = null;

    private $ipc_array = array();

    final public function __construct($logger, $multithread, $max_result_bytes_in_multithread, $max_childs_runtime) {

        $this->logger = $logger;

        $this->multithread = $multithread;

        $this->max_result_bytes_in_multithread = $max_result_bytes_in_multithread;

        $this->max_childs_runtime = $max_childs_runtime;

    }

    final public function addJob(\Comodojo\Extender\Job\Job $job) {

        $uid = self::getJobUid();

        try {

            $target = $job->getTarget();

            $class = $job->getClass();

            if ( class_exists("\\Comodojo\\Extender\\Task\\".$class) === false ) {

                if ( !file_exists($target) ) throw new Exception("Task file does not exists");

                if ( (include($target)) === false ) throw new Exception("Task file cannot be included");

            }

            $this->jobs[$uid] = array(
                "name"      =>  $job->getName(),
                "id"        =>  $job->getId(),
                "parameters"=>  $job->getParameters(),
                "task"      =>  $job->getTask(),
                "target"    =>  $target,
                "class"     =>  $class
            );

        }
        catch (Exception $e) {

            $this->logger->error('Error including job',array(
                "JOBUID"=> $uid,
                "ERROR" => $e->getMessage(),
                "ERRID" => $e->getCode()
            ));

            return false;

        }

        return $uid;

    }

    final public function free() {

        $this->jobs = array();
        $this->completed_processes = array();
        $this->running_processes = array();
        $this->forked_processes = array();
        $this->ipc_array = array();

    }

    public function run() {
        
        foreach ($this->jobs as $jobUid => $job) {
            
            if ( $this->multithread AND sizeof($this->jobs) > 1 ) {

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

        if ( $this->multithread ) $this->logger->info("Extender forked ".sizeof($this->forked_processes)." process(es) in the running queue", $this->forked_processes);

        $exec_time = microtime(true);

        while( !empty($this->running_processes) ) {

            foreach($this->running_processes as $pid => $job) {

                //$job[0] is name
                //$job[1] is uid
                //$job[2] is start timestamp
                //$job[3] is job id

                if( !$this->is_running($pid) ) {

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

                        $kill = $this->kill($pid);

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

        return $this->completed_processes;

    }

    public function runSinglethread($jobUid) {

        $job = $this->jobs[$jobUid];

        // get job start timestamp
        $start_timestamp = microtime(true);

        $name = $job['name'];

        $id = $job['id'];

        $parameters = $job['parameters'];

        $task = $job['task'];

        $class = $job['class'];

        $task_class = "\\Comodojo\\Extender\\Task\\".$class;

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

    public function runMultithread($jobUid) {

        $job = $this->jobs[$jobUid];

        // get job start timestamp
        $start_timestamp = microtime(true);

        $name = $job['name'];

        $id = $job['id'];

        $parameters = $job['parameters'];

        $task = $job['task'];

        $class = $job['class'];

        $task_class = "\\Comodojo\\Extender\\Task\\".$class;

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

            $this->adjustNiceness($pid);

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
    private final function is_running($pid) {

        return (pcntl_waitpid($pid, $this->status, WNOHANG) === 0);

    }

    private final function kill($pid) {

        if (function_exists("pcntl_signal")) return posix_kill($pid, SIGTERM); //JOB can handle the SIGTERM
        
        else return posix_kill($pid, SIGKILL); //JOB cannot handle the SIGTERM, so terminate it w SIGKILL

    }

    public final function killAll($parent_pid) {

        foreach ($this->running_processes as $pid => $process) {

            if ( $pid !== $parent_pid) posix_kill($pid, SIGTERM);

        }

    }

    static private function getJobUid() {

        return md5(uniqid(rand(), true), 0);

    }


    /**
     * Change child process priority according to EXTENDER_NICENESS
     *
     */
    private function adjustNiceness($pid) {

        if ( $this->multithread AND defined("EXTENDER_CHILD_NICENESS") ) {

            $niceness = pcntl_setpriority($pid, EXTENDER_CHILD_NICENESS);

            if ( $niceness == false ) $this->logger->warning("Unable to set child process ".$pid." niceness to ".EXTENDER_CHILD_NICENESS);

        }

    }

}
