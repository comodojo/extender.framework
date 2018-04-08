<?php namespace Comodojo\Extender\Socket\Commands\Scheduler;

use \Comodojo\Daemon\Daemon;
use \Comodojo\Extender\Schedule\Manager;
use \Comodojo\Extender\Socket\Messages\Task\Request as TaskRequestMessage;
use \Comodojo\Extender\Socket\Messages\Scheduler\Schedule as ScheduleMessage;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Exception\RpcException;
use \Exception;

class Get {

    public static function execute(Parameters $params, Daemon $daemon) {

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        $id = $params->get('id');
        $name = $params->get('name');

        $schedule = empty($id) ? $manager->getByName($name) :
                $manager->get($id);

        if ( empty($schedule) ) throw new RpcException("No record could be found", -31002);

        $request = $schedule->getRequest();

        $schedule_message = new ScheduleMessage();
        $schedule_message->setId($schedule->getId());
        $schedule_message->setName($schedule->getName());
        $schedule_message->setDescription($schedule->getDescription());
        $schedule_message->setExpression((string)$schedule->getExpression());
        $schedule_message->setEnabled($schedule->getEnabled());

        $request_message = $request->convertToMessage();

        return [$schedule_message->export(), $request_message->export()];

    }

}
