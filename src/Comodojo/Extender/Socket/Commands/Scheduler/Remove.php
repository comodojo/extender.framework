<?php namespace Comodojo\Extender\Socket\Commands\Scheduler;

use \Comodojo\Daemon\Daemon;
use \Comodojo\Extender\Schedule\Manager;
use \Comodojo\RpcServer\Request\Parameters;

class Remove {

    public static function execute(Parameters $params, Daemon $daemon) {

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        $id = $params->get('id');
        $name = $params->get('name');

        $remove = empty($id) ? $manager->removeByName($name) :
            $manager->remove($id);

        $refresh = Refresh::execute($params, $daemon);

        return $remove && $refresh;

    }

}
