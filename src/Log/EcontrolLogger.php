<?php namespace Comodojo\Extender\Log;

use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Comodojo\Extender\Log\ConsoleHandler;

/**
 * Create a logger instance for econtrol
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

class EcontrolLogger extends LogWrapper {

    /**
     * Create the logger
     *
     * @param string $level
     *
     * @return \Monolog\Logger
     */
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