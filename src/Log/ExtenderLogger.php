<?php namespace Comodojo\Extender\Log;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Handler\NullHandler;
use \Comodojo\Extender\Log\ConsoleHandler;

/**
 * Create a logger instance for extender
 * 
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ExtenderLogger extends LogWrapper {

    /**
     * Create the logger
     *
     * @param bool $verbose
     *
     * @return \Monolog\Logger
     */
    public static function create($verbose = false) {

        $enabled = defined('EXTENDER_LOG_ENABLED') ? filter_var(EXTENDER_LOG_ENABLED, FILTER_VALIDATE_BOOLEAN) : false;

        $name = defined('EXTENDER_LOG_NAME') ? EXTENDER_LOG_NAME : 'extender-default';

        $level = empty($force_level) ? self::getLevel(defined('EXTENDER_LOG_LEVEL') ? EXTENDER_LOG_LEVEL : 'DEBUG') : self::getLevel($force_level);

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