<?php namespace Comodojo\Extender\Workers;

use \Comodojo\Daemon\Worker\AbstractWorker;
use \Comodojo\Extender\Task\Manager as TaskManager;
use \Comodojo\Extender\Schedule\Manager as ScheduleManager;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Traits\WorkerTrait;

class ScheduleWorker extends AbstractWorker {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;
    use TasksTableTrait;
    use EntityManagerTrait;
    use WorkerTrait;

    protected $wakeup_time = 0;

    public function spinup() {

        $this->task_manager = new TaskManager(
            'schedule.worker',
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getTasksTable(),
            $this->getEvents(),
            $this->getEntityManager()
        );

        $this->getEvents()->subscribe('daemon.worker.refresh', '\Comodojo\Extender\Listeners\RefreshScheduler');

        $this->job_manager = new ScheduleManager(
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getEvents(),
            $this->getEntityManager()
        );

    }

    public function loop() {

        if ( $this->wakeup_time > time() ) {
            $this->logger->info('Still in sleep time, sorry');
            return;
        }

        $jobs = $this->job_manager->getJobs(true);

        if ( empty($jobs) ) {

            $this->logger->debug('Nothing to do right now, sleeping... zzZZzZzZzz');

        } else {

            $this->logger->debug(count($jobs)." jobs will be executed");

            $requests = $this->jobsToRequests($jobs);

            $this->task_manager->addBulk($requests);

            $result = $this->task_manager->run();

            $this->job_manager->updateSchedules($result);

        }

        $this->wakeup_time = $this->job_manager->getNextCycleTimestamp();

    }

    public function spindown() {

        unset($this->task_manager);
        unset($this->job_manager);

    }

    public function refreshPlans() {

        $this->wakeup_time = 0;

    }

}
