<?php namespace Comodojo\Extender\Socket\Commands\Scheduler;

use \Comodojo\Daemon\Daemon;
use \Comodojo\Extender\Schedule\Manager;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Exception\RpcException;
use \InvalidArgumentException;
use \Exception;

class Disable {

    public static function execute(Parameters $params, Daemon $daemon) {

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        $id = $params->get('id');
        $name = $params->get('name');

        try {

            $disable = empty($id) ? $manager->disableByName($name) :
                $manager->disable($id);

        } catch (InvalidArgumentException $iae) {
            throw new RpcException("No record could be found", -31002);
        } catch (Exception $e) {
            throw $e;
        }

        $refresh = Refresh::execute($params, $daemon);

        return $disable && $refresh;

    }

}
