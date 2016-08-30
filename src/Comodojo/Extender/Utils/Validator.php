<?php namespace Comodojo\Extender\Utils;

use \Cron\CronExpression;
use \Exception;

/**
 * Basic signaling for extender components
 *
 * @package     Comodojo Framework
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

class Validator {

    /**
     * Validate a cron expression and, if valid, return next run timestamp plus
     * an array of expression parts
     *
     * @param   string  $expression
     *
     * @return  array   Next run timestamp at first position, expression parts at second
     * @throws  \Exception
     */
    public static function cronExpression($expression) {

        try {

            $cron = CronExpression::factory($expression);

            $s = $cron->getNextRunDate()->format('c');

            $e = $cron->getExpression();

            $e_array = preg_split('/\s/', $e, -1, PREG_SPLIT_NO_EMPTY);

            $e_count = count($e_array);

            if ( $e_count < 5 || $e_count > 6 ) throw new Exception($e." is not a valid cron expression");

            if ( $e_count == 5 ) $e_array[] = "*";

        }
        catch (Exception $e) {

            throw $e;

        }

        return array($s, $e_array);

    }

    public static function laggerTimeout($timeout) {

        return filter_var($timeout, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 5,
                'min_range' => 0
            )
        ));

    }

    public static function maxChildRuntime($runtime) {

        return filter_var($runtime, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 600,
                'min_range' => 1
            )
        ));

    }

    public static function forkLimit($limit) {

        return filter_var($limit, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => 0
            )
        ));

    }

    public static function multithread($multithread) {

        return $multithread === true && Checks::multithread();

    }

}
