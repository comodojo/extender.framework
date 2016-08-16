<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Dispatcher\Components\Configuration;
use \Psr\Log\LoggerInterface;

/**
 * Job runner
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

class Runner {
    
    protected $configuration;
    
    protected $logger;
    
    public function __construct(Configuration $configuration, LoggerInterface $logger) {
        
        // init components
        $this->configuration = $configuration;
        
        $this->logger = $logger;
        
    }
    
    public function run($id, $task, $class, $timestamp = null, $name = null) {
        
        $this->logger->info("Starting task $task ($id) ");

        try {

            // create a task instance

            $thetask = new $class(
                $this->configuration,
                $this->logger,
                $name,
                $timestamp,
                $id,
                $parameters
            );

            // get the task pid (we are in singlethread mode)

            $pid = $thetask->pid;

            // run task

            $result = $thetask->start();
        
        } catch (TaskException $te) {

            $this->logger->notice("Job ".$job['name']."(".$job['id'].") ends with error");
        
            return array($pid, $name, false, $start_timestamp, $te->getEndTimestamp(), $te->getMessage(), $id, $te->getWorklogId());
        
        } catch (Exception $e) {

            $this->logger->notice("Job ".$job['name']."(".$job['id'].") ends with error");
        
            return array($pid, $name, false, $start_timestamp, null, $e->getMessage(), $id, null);
        
        }

        $this->logger->notice("Job ".$job['name']."(".$job['id'].") ends with ".($result["success"] ? "success" : "failure"));

        return array($pid, $name, $result["success"], $start_timestamp, $result["timestamp"], $result["result"], $id, $result["worklogid"]);
        
    }
    
}