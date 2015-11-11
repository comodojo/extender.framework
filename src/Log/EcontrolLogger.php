<?php namespace Comodojo\Extender\Log;

use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Comodojo\Extender\Log\ConsoleHandler;

class EcontrolLogger extends LogWrapper {

    public static function create($level = false) {

        $logger = new Logger("econtrol-default");

        if ( empty($level) ) {

            $logger->pushHandler(new NullHandler(self::getLevel("ERROR")));

        } else {

            $logger->pushHandler(new ConsoleHandler(self::getLevel($level)));

        }

        return $logger;

    }

}