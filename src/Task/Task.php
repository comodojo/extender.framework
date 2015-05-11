<?php namespace Comodojo\Extender\Task;

use \Comodojo\Exception\TaskException;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;

/**
 * Task class
 *
 * A task is an atomic PHP script that may be invoked in single or multithread mode.
 * 
 * In the first case, result and (eventually) exceptions are returned back to Extender (via JobRunner class)
 * directly; in second one, task is forked via pcntl and result is catched via finite stream.
 * This is the reason why task should give back a string.
 *
 * Each task manage its own worklog on database and may define a Monolog instance to log to.
 *
 * @package     Comodojo dispatcher (Spare Parts)
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

class Task {
        
    // Things a task may modify

    /**
     * The job name
     * 
     * @var string
     */
    private $name = 'EXTENDERTASK';
    
    /**
     * Job class (the one that extend cron_job).
     * 
     * @var string
     */
    private $class = null;

    /**
     * Start timestamp
     * 
     * @var int
     */
    private $start_timestamp = null;
    
    /**
     * End timestamp
     * 
     * @var int
     */
    private $end_timestamp = null;
    
    /**
     * Current process PID
     */
    private $pid = null;
    
    /**
     * The job result (if any)
     */
    private $job_result = null;
    
    /**
     * The job end state
     */
    private $job_success = false;
    
    /**
     * Worklog ID
     */
    private $worklog_id = null;
    
    /**
     * Parameters that extender may provide
     */
    private $parameters = array();

    /**
     * Database handler
     */
    private $dbh = array();
    
    /**
     * Task constructor.
     * 
     * @param   array   $parameters     Array of parameters (if any)
     * @param   int     $pid            Task PID (if any)
     * @param   string  $name           Task Name
     * @param   int     $timestamp      Start timestamp (if null will be retrieved directly)
     * @param   bool    $multithread    Multithread switch
     * 
     * @return  Object  $this 
     */
    final public function __construct($parameters, $pid=null, $name=null, $timestamp=null, $multithread=null) {
        
        // Setup task

        if ( !empty($parameters) ) $this->parameters = $parameters;
        
        if ( !is_null($name) ) $this->name = $name;
        
        $this->pid = is_null($pid) ? getmypid() : $pid;

        $this->start_timestamp = is_null($timestamp) ? microtime(true) : $timestamp;

        $this->class = get_class($this);

        // Setup database (worklog!)

        try{

            $this->dbh = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        // Setup an exit strategy if multithread enabled (parent may kill child process if timeout exceeded)

        if ( filter_var($multithread, FILTER_VALIDATE_BOOLEAN) ) {

            pcntl_signal(SIGTERM, function() {

                $end = microtime(true);

                if ( !is_null($this->worklog_id) ) $this->closeWorklog($this->worklog_id, false, 'Job killed by parent (timeout exceeded)', $end);

                exit(1);

            });

        }

    }

    /**
     * Class destructor, just to unset database handler
     * 
     */
    final public function __destruct() {

        unset($this->dbh);

    }

    /**
     * Get all provided parameters in array
     * 
     * @return  Array
     */
    final public function getParameters() {

        return $this->parameters;

    }

    /**
     * Get a provided parameter's value
     * 
     * @return  mixed   parameter value if provided or null otherwise
     */
    final public function getParameter($parameter) {

        if ( array_key_exists($parameter, $this->parameters) ) return $this->parameters[$parameter];

        else return null;

    }

    /**
     * Return PID from system (null if no multi-thread active)
     * 
     * @return int
     */
    final public function getPid() {

        return $this->pid;

    }

    /**
     * Start task!
     * 
     * @return  array
     */
    final public function start() {

        try{

            $job_run_info = $this->execTask();

        }
        catch (Exception $e) {

            throw new TaskException($e->getMessage(), $e->getCode());
            
        }

        return $job_run_info;

    } 
    
    /**
     * Execute task.
     *
     * This method provides to:
     * - setup worklog
     * - invoke method "run", that should be defined in task implementation
     */
    private function execTask() {

        try{

            // open worklog

            $this->worklog_id = $this->createWorklog($this->pid, $this->name, $this->class, $this->start_timestamp);

            $this->result = $this->run();

            $this->end_timestamp = microtime(true);

            $this->closeWorklog($this->worklog_id, true, $this->result, $this->end_timestamp);

        }
        catch (Exception $e) {

            $this->result = $e->getMessage();

            if ( !is_null($this->worklog_id) ) {

                if ( is_null($this->end_timestamp) ) $this->end_timestamp = microtime(true);

                $this->closeWorklog($this->worklog_id, false, $this->result, $this->end_timestamp);

            }

            throw $e;

        }

        return Array(
            "success"   =>  true,
            "timestamp" =>  $this->end_timestamp,
            "result"    =>  $this->result
        );

    }

    public function run() {

        return;

    }

    /**
     * Create the worklog for current job
     */
    private function createWorklog($pid, $name, $class, $start_timestamp) {
        
        try{

            // $db = new EnhancedDatabase(
            //     EXTENDER_DATABASE_MODEL,
            //     EXTENDER_DATABASE_HOST,
            //     EXTENDER_DATABASE_PORT,
            //     EXTENDER_DATABASE_NAME,
            //     EXTENDER_DATABASE_USER,
            //     EXTENDER_DATABASE_PASS
            // );

            $w_result = $this->dbh->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
                ->keys(array("pid","name","task","status","start"))
                ->values(array($pid, $name, $class, 'STARTED', $start_timestamp))
                ->store();

        }
        catch (DatabaseException $de) {
            
            //unset($db);

            throw $de;

        }
        
        //unset($db);

        return $w_result['id'];
            
    }
    
    /**
     * Close worklog for current job
     */
    private function closeWorklog($worklog_id, $success, $result, $end_timestamp) {
        
        try{
            
            // $db = new EnhancedDatabase(
            //     EXTENDER_DATABASE_MODEL,
            //     EXTENDER_DATABASE_HOST,
            //     EXTENDER_DATABASE_PORT,
            //     EXTENDER_DATABASE_NAME,
            //     EXTENDER_DATABASE_USER,
            //     EXTENDER_DATABASE_PASS
            // );

            $w_result = $this->dbh->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
                ->keys(array("status", "success", "result", "end"))
                ->values(array("FINISHED", $success, $result, $end_timestamp))
                ->where( "id", "=", $worklog_id )
                ->update();

        }
        catch (DatabaseException $de) {
            
            //unset($db);
            
            throw $de;

        }
        
        //unset($db);
        
    }
    
}
