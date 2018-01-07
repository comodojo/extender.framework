<?php namespace Comodojo\Extender\Socket\Commands\Queue;

use \Comodojo\Daemon\Daemon;
use \Comodojo\RpcServer\Request\Parameters;

class Info {

    public static function execute(Parameters $params, Daemon $daemon) {

        $configuration = $daemon->getConfiguration();
        $base_path = $configuration->get('base-path');
        $lock_path = $configuration->get('run-path');
        $lock_file = "$base_path/$lock_path/queue.worker.lock";

        return unserialize(file_get_contents($lock_file));

    }

}
