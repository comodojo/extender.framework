<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Extender\Tasks\Table as TasksTable;
use \Comodojo\Extender\Components\Database;
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

    protected $dbh;

    protected $worklog;

    protected $tasks;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $tasks,
        Connection $dbh = null
    ) {

        // init components
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->tasks = $tasks;

        // init database
        $this->dbh = is_null($dbh) ? Database::init($configuration) : $dbh;

        // init worklog manager
        $this->worklog = new Worklog($this->dbh, $this->configuration->get('database-worklogs-table'));

    }

    public function run($name, $task, $jid = null, $parameters = array()) {

        try {

            // retrieve the task class
            $class = $this->tasks->get($task)->class;

            $start = microtime(true);

            // create a task instance
            $thetask = new $class(
                $this->logger,
                $name,
                $parameters
            );

            // get the task pid
            $pid = $thetask->pid;

            $this->logger->info("Starting task $task ($class) with pid $pid");

            $wid = $this->worklog->open($pid, $name, $jid, $task, $start);

            // run task
            $result = $thetask->run();

            $status = true;

        } catch (TaskException $te) {

            $status = false;

            $result = $te->getMessage();

        } catch (Exception $e) {

            $status = false;

            $result = $e->getMessage();

        } finally {

            if ( $wid ) $this->worklog->close($wid, true, $result, microtime(true));

        }

        $this->logger->notice("Task $name ($task) with pid $pid ends in ".$status ? 'success' : 'error');

        return new Result(
            array (
                $pid,
                $name,
                $success,
                $start,
                $end,
                $result,
                $wid
            )
        );

    }

}
