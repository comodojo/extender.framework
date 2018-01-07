<?php namespace Comodojo\Extender\Socket\Commands\Scheduler;

use \Comodojo\Extender\Schedule\Manager;
use \Comodojo\Daemon\Daemon;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Extender\Socket\Messages\Task\Request as TaskRequestMessage;
use \Comodojo\Extender\Socket\Messages\Scheduler\Schedule as ScheduleMessage;
use \Comodojo\Extender\Task\Request as TaskRequest;
use \Comodojo\Extender\Orm\Entities\Schedule;
use \Cron\CronExpression;
use \Comodojo\Exception\RpcException;
use \Exception;

class Edit {

    public static function execute(Parameters $params, Daemon $daemon) {

        $schedule_message = $params->get('schedule');
        $request_message = $params->get('request');

        if (
            empty($schedule_message['id']) ||
            empty($schedule_message['name']) ||
            empty($schedule_message['expression'])
        ) {
            throw new RpcException("Missing schedule name, id or invalid expression", -32600);
        }

        try {

            $request = TaskRequest::createFromMessage(
                TaskRequestMessage::createFromExport($request_message)
            );

        } catch (Exception $e) {
            throw new RpcException("Invalid message payload in request", -32600);
        }

        try {

            $schedule = new Schedule();
            $schedule->setId($schedule_message['id']);
            $schedule->setName($schedule_message['name']);
            $schedule->setExpression(CronExpression::factory($schedule_message['expression']));
            $schedule->setDescription($schedule_message['description']);
            $schedule->setEnabled($schedule_message['enabled']);
            $schedule->setRequest($request);

        } catch (Exception $e) {
            throw new RpcException("Invalid message payload in schedule", -32600);
        }

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        try {
            $result = $manager->edit($schedule);
        } catch (Exception $e) {
            throw new RpcException($e->getMessage(), -32500);
        }

        $refresh = Refresh::execute($params, $daemon);

        // should method ignore invalid refresh message here?

        return $result;

    }

}
