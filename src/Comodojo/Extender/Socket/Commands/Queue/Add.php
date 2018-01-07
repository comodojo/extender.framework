<?php namespace Comodojo\Extender\Socket\Commands\Queue;

use \Comodojo\Extender\Queue\Manager;
use \Comodojo\Daemon\Daemon;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Extender\Socket\Messages\Task\Request as TaskRequestMessage;
use \Comodojo\Extender\Task\Request as TaskRequest;
use \Comodojo\Exception\RpcException;
use \Exception;

class Add {

    public static function execute(Parameters $params, Daemon $daemon) {

        $message = $params->get('request');

        try {
            $request = TaskRequest::createFromMessage(
                TaskRequestMessage::createFromExport($message)
            );
        } catch (Exception $e) {
            throw new RpcException("Invalid message payload in request", -32600);
        }

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        return $manager->add($request);

    }

}
