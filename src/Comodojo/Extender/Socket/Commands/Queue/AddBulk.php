<?php namespace Comodojo\Extender\Socket\Commands\Queue;

use \Comodojo\Extender\Queue\Manager;
use \Comodojo\Daemon\Daemon;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Extender\Socket\Messages\Task\Request as TaskRequestMessage;
use \Comodojo\Extender\Task\Request as TaskRequest;
use \Comodojo\Exception\RpcException;
use \Exception;

class AddBulk {

    public static function execute(Parameters $params, Daemon $daemon) {

        $messages = $params->get('requests');

        $requests = [];

        try {
            foreach ($messages as $message) {
                $requests[] = TaskRequest::createFromMessage(
                    TaskRequestMessage::createFromExport($message)
                );
            }
        } catch (Exception $e) {
            throw new RpcException("Invalid message payload in request", -32600);
        }

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        return $manager->addBulk($requests);

    }

}
