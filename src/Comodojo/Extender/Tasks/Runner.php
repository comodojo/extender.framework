<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Extender\Tasks\Table as TasksTable;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Events\TaskEvent;
use \Comodojo\Extender\Events\TaskStatusEvent;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\EventsManager;
use \Psr\Log\LoggerInterface;
use \Doctrine\DBAL\Connection;
use \Exception;

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

    protected $events;

    protected $dbh;

    protected $worklog;

    protected $tasks;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $tasks,
        EventsManager $events,
        Connection $dbh = null
    ) {

        // init components
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->events = $events;
        $this->tasks = $tasks;

        // init database
        $this->dbh = is_null($dbh) ? Database::init($configuration) : $dbh;

        // init worklog manager
        $this->worklog = new Worklog($this->dbh, $this->configuration->get('database-worklogs-table'));

    }

    public function run($name, $task, $jid = null, $parameters = array()) {

        try {

            $start = microtime(true);

            $this->logger->info("Starting new task $task ($name)");

            $thetask = $this->createTask($name, $task, $parameters);

            $this->events->emit( new TaskEvent('start', $thetask) );

            $pid = $thetask->pid;

            $wid = $this->worklog->open($pid, $name, $jid, $task, $parameters, $start);

            $status = true;

            $this->events->emit( new TaskStatusEvent('start', $thetask) );

            try {

                // run task
                $result = $thetask->run();

            } catch (TaskException $te) {

                $status = false;

                $result = $te->getMessage();

            } catch (Exception $e) {

                $status = false;

                $result = $e->getMessage();

            }

            $this->events->emit( new TaskStatusEvent($status ? 'success' : 'error', $thetask) );

            $this->events->emit( new TaskStatusEvent('stop', $thetask) );

            $this->events->emit( new TaskEvent('stop', $thetask) );

            $end = microtime(true);

            $this->worklog->close($wid, $status, $result, $end);

            $this->logger->notice("Task $name ($task) with pid $pid ends in ".($status ? 'success' : 'error'));

        } catch (Exception $e) {

            throw $e;

        }

        return new Result(
            array (
                $pid,
                $name,
                $status,
                $start,
                $end,
                $result,
                intval($wid)
            )
        );

    }

    private function createTask($name, $task, $parameters) {

        // get the Task
        $task_entry = $this->tasks->get($task);

        if ( is_null($task_entry) ) {
            throw new Exception("Cannot find task $task");
        }

        // retrieve the task class
        $class = $task_entry->class;

        // create a task instance
        $thetask = new $class(
            $this->logger,
            $name,
            $parameters
        );

        if ( !($thetask instanceof \Comodojo\Extender\Tasks\TaskInterface) ) {
            throw new Exception("Invalid task object $class");
        }

        return $thetask;

    }

}
