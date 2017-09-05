<?php namespace Comodojo\Extender\Workers;

use \Comodojo\Daemon\Worker\AbstractWorker;
use \Comodojo\Extender\Task\Manager as TaskManager;
use \Comodojo\Extender\Queue\Manager as QueueManager;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Traits\WorkerTrait;

class QueueWorker extends AbstractWorker {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;
    use TasksTableTrait;
    use WorkerTrait;

    public function loop() {

        $task_manager = new TaskManager(
            'queue.worker',
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getTasksTable(),
            $this->getEvents()
        );

        $job_manager = new QueueManager(
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getEvents()
        );

        $queue = $job_manager->get();

        $requests = $this->jobsToRequests($queue);

        $job_manager->remove($queue);

        $task_manager->addBulk($requests);

        $result = $task_manager->run();

        unset($task_manager);
        unset($job_manager);

    }

}
