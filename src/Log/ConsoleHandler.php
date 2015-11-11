<?php namespace Comodojo\Extender\Log;

use \Monolog\Logger;
use \Monolog\Handler\AbstractProcessingHandler;
use \Console_Color2;

/**
 * Log-to-console handler
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

class ConsoleHandler extends AbstractProcessingHandler {

    private $color = null;

    private static $colors = array(
        100 => '%8',
        200 => '%g',
        250 => '%U',
        300 => '%Y',
        400 => '%r',
        500 => '%R',
        550 => '%m',
        600 => '%M',
    );

    public function __construct($level = Logger::DEBUG, $bubble = true) {

        $this->color = new Console_Color2();

        parent::__construct($level, $bubble);

    }

    protected function write(array $record) {
        
        $level = $record['level'];

        $message = $record['formatted'];

        $context = empty($record['context']) ? null : $record['context'];

        $time = $record['datetime']->format('c');

        $this->toConsole($time, $level, $message, $context);

    }

    private function toConsole($time, $level, $message, $context) {

        print $this->color->convert(static::$colors[$level].$message."%n");

        if ( !empty($context) ) print $this->color->convert(static::$colors[$level].var_export($context, true)."%n\n");

    }

}
