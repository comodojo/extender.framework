<?php namespace Comodojo\Extender\Workers;

use \Comodojo\Daemon\Worker\AbstractWorker;
use \Comodojo\Extender\Task\Manager as TaskManager;
use \Comodojo\Extender\Queue\Manager as QueueManager;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Traits\WorkerTrait;

class QueueWorker extends AbstractWorker {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;
    use TasksTableTrait;
    use EntityManagerTrait;
    use WorkerTrait;

    public function spinup() {

        $this->task_manager = new TaskManager(
            'queue.worker',
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getTasksTable(),
            $this->getEvents(),
            $this->getEntityManager()
        );

        $this->job_manager = new QueueManager(
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getEvents(),
            $this->getEntityManager()
        );

    }

    public function loop() {

        $queue = $this->job_manager->get();

        $requests = $this->jobsToRequests($queue);

        $this->job_manager->remove($queue);

        $this->task_manager->addBulk($requests);

        $result = $this->task_manager->run();

    }

    public function spindown() {

        unset($this->task_manager);
        unset($this->job_manager);

    }

}
