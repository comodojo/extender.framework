<?php namespace Comodojo\Extender;

/**
 * Write plan file (timestamp of next planned job)
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

class Planner {

    /**
     * Standard dump file, will be placed in EXTENDER_CACHE_FOLDER
     *
     * @var     string
     */
    private static $planfile = "extender.plans";

    /**
     * Store timestamp of next planned job
     *
     * @param   int     $planned
     * 
     * @return bool
     */
    final public static function set($planned) {

        $planfile = EXTENDER_CACHE_FOLDER . self::$planfile;

        return file_put_contents($planfile, $planned);

    }

    /**
     * Get the timestamp of next planned job 
     *
     * @return int
     */
    final public static function get() {

        $planfile = EXTENDER_CACHE_FOLDER.self::$planfile;

        $plans = null;

        if ( file_exists($planfile) ) $plans = file_get_contents($planfile);

        return intval($plans);

    }

    /**
     * Remove the plan file
     *
     * @return  bool
     */
    final public static function release() {

        $planfile = EXTENDER_CACHE_FOLDER.self::$planfile;

        if ( file_exists($planfile) ) return unlink($planfile);
        
        else return true;

    }

}
