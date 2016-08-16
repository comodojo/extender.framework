<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Extender\Components\Parameters;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Psr\Log\LoggerInterface;

/**
 * Task object
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

abstract class Task {

    use TimestampTrait;

    public $parameters;
    
    public $name;
    
    public $jobid;
    
    public $pid;
    
    protected $configuration;
    
    protected $logger;
    
    private $worklog;
    
    private $worklog_id;

    /**
     * Task constructor.
     * 
     * @param   array           $parameters     Array of parameters (if any)
     * @param   \Monolog\Logger $logger
     * @param   int             $pid            Task PID (if any)
     * @param   string          $name           Task Name
     * @param   int             $timestamp      Start timestamp (if null will be retrieved directly)
     * @param   bool            $multithread    Multithread switch
     * 
     * @return  Object  $this 
     */
    final public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        $name = 'EXTENDERTASK',
        $timestamp = null,
        $jobid = null,
        $parameters = array()
    ) {
        
        // Setup task
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->parameters = new Parameters($parameters);
        $this->worklog = new Worklog($configuration, $logger);
        
        $this->name = $name;
        $this->setTimestamp($timestamp);
        $this->pid = getmypid();

        // Setup an exit strategy if multithread enabled (parent may kill child process if timeout exceeded)
        
        pcntl_signal(SIGTERM, function() {

            $end = microtime(true);

            if ( !is_null($this->worklog_id) ) $this->worklog->close($this->worklog_id, false, 'Job killed (timeout exceeded?)', $end);

            exit(1);

        });

    }
    
    /**
     * The run method; SHOULD be implemented by each task
     */
    abstract public function run();
    
    
    /**
     * Start task!
     * 
     * @return  array
     */
    final public function start() {

        try {

            return $this->execTask();

        } catch (Exception $e) {

            throw new TaskException($e->getMessage(), $e->getCode(), $e, $this->worklog_id);
            
        }
        
    }
    
    /**
     * Execute task.
     *
     * This method provides to:
     * - setup worklog
     * - invoke method "run", that should be defined in task implementation
     */
    private function execTask() {

        try {

            // open worklog

            $this->worklog_id = $this->worklog->create($this->pid, $this->name, $this->class, $this->start_timestamp);

            $this->result = $this->run();

            $this->end_timestamp = microtime(true);

            $this->closeWorklog(true);

        } catch (Exception $e) {

            $this->result = $e->getMessage();

            if ( !is_null($this->worklog_id) ) {

                if ( is_null($this->end_timestamp) ) $this->end_timestamp = microtime(true);

                $this->closeWorklog(false);

            }

            throw $e;

        }

        return array(
            "success"   => true,
            "timestamp" => $this->end_timestamp,
            "result"    => $this->result,
            "worklogid" => $this->worklog_id
        );

    }
    
}