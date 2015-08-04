<?php namespace Comodojo\Extender;

/**
 * Dump information about live extender status
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

class Status {

    /**
     * Standard dump file, will be placed in EXTENDER_CACHE_FOLDER
     *
     * @var     string
     */
    private static $statusfile = "extender.status";

    /**
     * Dump live informations from extender process (if in daemon mode and pcntl installed)
     *
     * @param   int     $timestamp_absolute
     * @param   itn     $parent_pid
     * @param   int     $completed_processes
     * @param   itn     $failed_processes
     */
    final public static function dump($timestamp_absolute, $parent_pid, $completed_processes, $failed_processes, $paused) {

        $statusfile = EXTENDER_CACHE_FOLDER.self::$statusfile;

        $data = array(
            "RUNNING"   =>  $paused ? 0 : 1,
            "STARTED"   =>  $timestamp_absolute,
            "TIME"      =>  microtime(true) - $timestamp_absolute,
            "PARENTPID" =>  $parent_pid,
            "COMPLETED" =>  $completed_processes,
            "FAILED"    =>  $failed_processes,
            "CPUAVG"    =>  sys_getloadavg(),
            "MEM"       =>  memory_get_usage(true),
            "MEMPEAK"   =>  memory_get_peak_usage(true),
            "USER"      =>  get_current_user(),
            "NICENESS"  =>  function_exists('pcntl_getpriority') ? pcntl_getpriority() : "UNDEFINED"
        );

        $content = serialize($data);

        return file_put_contents($statusfile, $content);

    }

    /**
     * Remove the status file
     *
     * @return  bool
     */
    final public static function release() {

        $statusfile = EXTENDER_CACHE_FOLDER.self::$statusfile;

        if ( file_exists($statusfile) ) return unlink($statusfile);
        
        else return false;

    }

}
