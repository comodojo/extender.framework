<?php namespace Comodojo\Extender\Jobs;

use \Comodojo\Extender\Components\Ipc;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Tasks\Table as TasksTable;
use \Comodojo\Extender\Tasks\Runner as TasksRunner;
use \Comodojo\Extender\Utils\ProcessTools;
use \Comodojo\Extender\Utils\Checks;
use \Comodojo\Extender\Events\JobEvent;
use \Comodojo\Extender\Events\JobStatusEvent;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\EventsManager;
use \Psr\Log\LoggerInterface;
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

    protected $tasks;

    protected $events;

    protected $ipc;

    protected $manager;

    protected $lagger_timeout;

    protected $max_runtime;

    protected $max_childs;

    private $multithread;

    private $runner;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $tasks,
        EventsManager $events
    ) {

        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->tasks = $tasks;
        $this->events = $events;
        $this->ipc = new Ipc($configuration);
        $this->manager = new Manager($this->configuration, $this->logger);

        // init the runner
        $this->runner = new TasksRunner(
            $this->configuration,
            $this->logger,
            $this->tasks,
            $this->events,
            Database::init($configuration)
        );

        // retrieve parameters
        $this->lagger_timeout = self::getLaggerTimeout($this->configuration);
        $this->multithread = self::getMultithread($this->configuration);
        $this->max_runtime = self::getMaxRuntime($this->configuration);
        $this->max_childs = self::getForkLimit($this->configuration);

        $this->logger->debug("Jobs runner online", array(
            'lagger_timeout' => $this->lagger_timeout,
            'multithread' => $this->multithread,
            'max_runtime' => $this->max_runtime,
            'max_childs' => $this->max_childs
        ));

    }

    public function __destruct() {

        $this->manager->release();

    }

    public function add(Job $job) {

        if ( $this->tasks->get($job->task) === null ) {

            $this->logger->error("Cannot add job ".$job->name.": missing task ".$job->task);

            return false;

        }

        return $this->manager->isQueued($job);

    }

    public function run() {

        foreach ($this->manager->queued() as $uid => $job) {

            if ( $this->multithread === false ) {

                $pid = ProcessTools::getPid();
                $this->manager->isStarting($uid, $pid);

                $result = $this->runner->run(
                    $job->name,
                    $job->task,
                    $job->id,
                    $job->parameters
                );

                $this->manager->isCompleted($uid, $result->success, $result->result, $result->wid);

                continue;

            }

            try {

                $pid = $this->forker($job);

            } catch (Exception $e) {

                $this->manager->isAborted($uid, $e->getMessage());

                continue;

            }

            $this->manager->isStarting($uid, $pid);

            if ( $this->max_childs > 0 && count($this->manager->running()) >= $this->max_childs ) {

                while( count($this->manager->running()) >= $this->max_childs ) {

                    $this->catcher();

                }

            }

        }

        if ( $this->multithread === true ) $this->catcher_loop();

        return array_values($this->manager->completed());

    }

    private function forker(Job $job) {

        //$this->logger->notice("Starting job ".$job->name."(".$job['id'].")");

        $uid = $job->uid;

        try {

            $this->ipc->init($uid);

        } catch (Exception $e) {

            $this->logger->error("Aborting job ".$job->name.": ".$e->getMessage());

            $this->ipc->hang($uid);

            throw $e;

        }

        $pid = pcntl_fork();

        if ( $pid == -1 ) {

            // $this->logger->error("Could not fok job, aborting");

            throw new Exception("Unable to fork job, aborting");

        } elseif ( $pid ) {

            //PARENT will take actions on processes later

            $niceness = $job->niceness;

            if ( $niceness !== null ) ProcessTools::setNiceness($niceness, $pid);

        } else {

            $this->ipc->close($uid, Ipc::READER);

            $result = $this->runner->run(
                $job->name,
                $job->task,
                $job->id,
                $job->parameters
            );

            $output = array(
                'success' => $result->success,
                'result' => $result->result,
                'wid' => $result->wid
            );

            $this->ipc->write($uid, serialize($output));

            $this->ipc->close($uid, Ipc::WRITER);

            exit(!$result->success);

        }

        return $pid;

    }

    private function catcher_loop() {

        while ( !empty($this->manager->running()) ) {

            $this->catcher();

        }

    }

    /**
     * Catch results from completed jobs
     *
     */
    private function catcher() {

        foreach ( $this->manager->running() as $uid => $job ) {

            if ( ProcessTools::isRunning($job->pid) === false ) {

                $this->ipc->close($uid, Ipc::WRITER);

                try {

                    $raw_output = $this->ipc->read($uid);

                    $output = unserialize(rtrim($raw_output));

                    $this->ipc->close($uid, Ipc::READER);

                    $success = $output["success"];
                    $result = $output["result"];
                    $wid = $output["wid"];

                } catch (Exception $e) {

                    $success = false;
                    $result = $e->getMessage();
                    $wid = null;

                    $this->logger->error($result);

                }

                $this->manager->isCompleted($uid, $success, $result, $wid);

                $status = $success ? 'success' : 'error';

                $this->logger->notice("Job ".$job->name."(id: ".$job->id.", uid: $uid) ends in $status");

            } else {

                $current_time = microtime(true);

                $maxtime = is_null($job->maxtime) ? $this->max_runtime : $job->maxtime;

                if ( $current_time > $job->start_timestamp + $maxtime ) {

                    $this->logger->warning("Killing pid ".$job->pid." due to maximum exec time reached", array(
                        "START_TIME"    => $job->start_timestamp,
                        "CURRENT_TIME"  => $current_time,
                        "MAX_RUNTIME"   => $maxtime
                    ));

                    $kill = ProcessTools::kill($job->pid, $this->lagger_timeout);

                    if ( $kill ) {
                        $this->logger->warning("Pid ".$job->pid." killed");
                    } else {
                        $this->logger->warning("Pid ".$job->pid." could not be killed");
                    }

                    $this->ipc->hang($uid);

                    $this->manager->isCompleted($uid, false, "Job killed due to max runtime reached");

                    $this->logger->notice("Job ".$job->name."(id: ".$job->id.", uid: $uid) ends in error");

                }

            }

        }

    }

    public function free() {

        $this->ipc->free();
        $this->manager->free();

    }

    private static function getLaggerTimeout(Configuration $configuration) {

        return filter_var($configuration->get('child-lagger-timeout'), FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 5,
                'min_range' => 0
            )
        ));

    }

    private static function getMaxRuntime(Configuration $configuration) {

        return filter_var($configuration->get('child-max-runtime'), FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 600,
                'min_range' => 1
            )
        ));

    }

    private static function getForkLimit(Configuration $configuration) {

        return filter_var($configuration->get('fork-limit'), FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => 0
            )
        ));

    }

    private static function getMultithread(Configuration $configuration) {

        return $configuration->get('multithread') === true && Checks::multithread();

    }

}
