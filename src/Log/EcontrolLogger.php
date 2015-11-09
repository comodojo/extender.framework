<?php namespace Comodojo\Extender\Log;

use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Comodojo\Extender\Log\ConsoleHandler;

class EcontrolLogger extends LogWrapper {

    public static function create($verbose = false, $force_level = false) {

        $level = empty($force_level) ? self::getLevel('DEBUG') : self::getLevel($force_level);

        $logger = new Logger("econtrol-default");

        if ( $verbose ) {

            $logger->pushHandler(new ConsoleHandler($level));

        } else {

            $logger->pushHandler(new NullHandler($level));

        }

        return $logger;

    }

}