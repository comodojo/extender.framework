<?php namespace Comodojo\Extender;

/**
 * Write plan file (timestamp of next planned job)
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
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
    static private $planfile = "extender.plans";

    /**
     * Dump live informations from extender process (if in daemon mode and pcntl installed)
     *
     * @param   int     $timestamp_absolute
     * @param   itn     $parent_pid
     * @param   int     $completed_processes
     * @param   itn     $failed_processes
     */
    static public final function set($planned) {

        $planfile = EXTENDER_CACHE_FOLDER.self::$planfile;

        return file_put_contents($planfile, $planned);

    }

    /**
     * 
     *
     * @param   int     $timestamp_absolute
     * @param   itn     $parent_pid
     * @param   int     $completed_processes
     * @param   itn     $failed_processes
     */
    static public final function get() {

        $planfile = EXTENDER_CACHE_FOLDER.self::$planfile;

        $plans = null;

        if ( file_exists($planfile) ) $plans = file_get_contents($planfile);

        return intval($plans);

    }

    /**
     * Remove the status file
     *
     * @return  bool
     */
    static final public function release() {

        $planfile = EXTENDER_CACHE_FOLDER.self::$planfile;

        if ( file_exists($planfile) ) return unlink($planfile);
        
        else return true;

    }

}
