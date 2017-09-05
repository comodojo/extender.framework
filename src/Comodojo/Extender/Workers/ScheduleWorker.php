<?php namespace Comodojo\Extender\Workers;

use \Comodojo\Daemon\Worker\AbstractWorker;
use \Comodojo\Extender\Task\Manager as TaskManager;
use \Comodojo\Extender\Schedule\Manager as ScheduleManager;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Traits\WorkerTrait;

class ScheduleWorker extends AbstractWorker {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;
    use TasksTableTrait;
    use WorkerTrait;

    protected $wakeup_time = 0;

    public function loop() {

        if ( $this->wakeup_time > time() ) {
            $this->logger->debug('Still in sleep time, next planned wakeup is '.date('r', $this->wakeup_time));
            return;
        }

        $task_manager = new TaskManager(
            'schedule.worker',
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getTasksTable(),
            $this->getEvents()
        );

        $this->getEvents()->subscribe('daemon.worker.refresh', '\Comodojo\Extender\Listeners\RefreshScheduler');

        $job_manager = new ScheduleManager(
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getEvents()
        );

        $jobs = $job_manager->getAll(true);

        if ( empty($jobs) ) {

            $this->logger->debug('Nothing to do right now, sleeping... zzZZzZzZzz');

        } else {

            $this->logger->debug(count($jobs)." jobs will be executed");

            $requests = $this->jobsToRequests($jobs);

            $task_manager->addBulk($requests);

            $result = $task_manager->run();

            $job_manager->updateFromResults($result);

        }

        $this->wakeup_time = $job_manager->getNextCycleTimestamp();

        unset($task_manager);
        unset($job_manager);

    }

    public function refreshPlans() {

        $this->wakeup_time = 0;

    }

}
