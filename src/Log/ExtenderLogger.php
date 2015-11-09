<?php namespace Comodojo\Extender\Log;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Handler\NullHandler;
use \Comodojo\Extender\Log\ConsoleHandler;

class ExtenderLogger extends LogWrapper {

    public static function create($verbose = false, $force_level = false) {

        $enabled = defined('EXTENDER_LOG_ENABLED') ? filter_var(EXTENDER_LOG_ENABLED, FILTER_VALIDATE_BOOLEAN) : false;

        $name = defined('EXTENDER_LOG_NAME') ? EXTENDER_LOG_NAME : 'extender-default';

        $level = self::getLevel(defined('EXTENDER_LOG_LEVEL') ? EXTENDER_LOG_LEVEL : 'DEBUG');

        $target = defined('EXTENDER_LOG_TARGET') ? EXTENDER_LOG_TARGET : null;

        $logger = new Logger($name);

        if ( $enabled ) {

            if ( is_null($target) ) {

                $logger->pushHandler(new ErrorLogHandler());

            } else {

                $logger->pushHandler(new StreamHandler(defined('EXTENDER_LOG_FOLDER') ? EXTENDER_LOG_FOLDER.$target : $target, $level));

            }

        }

        if ( $verbose ) {

            $logger->pushHandler(new ConsoleHandler($level));

        } else {

            $logger->pushHandler(new NullHandler($level));

        }

        return $logger;

    }

}