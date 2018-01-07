<?php namespace Comodojo\Extender\Socket\Commands\Scheduler;

use \Comodojo\Daemon\Daemon;
use \Comodojo\Extender\Schedule\Manager;
use \Comodojo\Extender\Socket\Messages\Task\Request as TaskRequestMessage;
use \Comodojo\Extender\Socket\Messages\Scheduler\Schedule as ScheduleMessage;
use \Comodojo\RpcServer\Request\Parameters;

class GetList {

    public static function execute(Parameters $params, Daemon $daemon) {

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        $schedule_messages = [];
        $schedules = $manager->getAll();

        foreach ($schedules as $schedule) {
            $schedule_message = new ScheduleMessage();
            $schedule_message->setId($schedule->getId());
            $schedule_message->setName($schedule->getName());
            $schedule_message->setDescription($schedule->getDescription());
            $schedule_message->setExpression((string)$schedule->getExpression());
            $schedule_message->setEnabled($schedule->getEnabled());
            $schedule_messages[] = $schedule_message->export();
        }

        return $schedule_messages;

    }

}
