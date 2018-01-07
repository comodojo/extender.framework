<?php namespace Comodojo\Extender\Socket\Commands\Scheduler;

use \Comodojo\Daemon\Daemon;
use \Comodojo\RpcServer\Request\Parameters;

class Refresh {

    public static function execute(Parameters $params, Daemon $daemon) {

        return $daemon->getWorkers()
            ->get("scheduler")
            ->getOutputChannel()
            ->send('refresh') > 0;

    }

}
