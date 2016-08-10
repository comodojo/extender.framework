<?php namespace Comodojo\Extender\Console;

/**
 * @package     Comodojo Extender
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


trait Arguments {

    protected $console_arguments = array(
        'verbose' => [
            'prefix' => 'v',
            'longPrefix' => 'verbose',
            'description' => 'verbose mode',
            'required' => false,
            'noValue' => true
        ],
        'debug' => [
            'prefix' => 'd',
            'longPrefix' => 'debug',
            'description' => 'debug mode',
            'required' => false,
            'noValue' => true
        ],
        'iterations' => [
            'prefix' => 'i',
            'longPrefix' => 'iterations',
            'description' => 'number of iterations that daemon will do',
            'required' => false,
            'defaultValue' => 0,
            'castTo' => 'int'
        ],
        'summary' => [
            'prefix' => 's',
            'longPrefix' => 'summary',
            'description' => 'show summary of executed jobs (if any)',
            'required' => false,
            'noValue' => true
        ],
        'help' => [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'show this help',
            'required' => false,
            'noValue' => true
        ]
    );

}
