<?php namespace Comodojo\Extender;

/**
 * Dump information about live extender status
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

class Status {

    static private $statusfile = "extender.status";

    static public final function dump($timestamp_absolute, $parent_pid, $completed_processes, $failed_processes) {

        $statusfile = EXTENDER_CACHE_FOLDER.self::$statusfile;

        $data = array(
        	"STARTED"   =>  $timestamp_absolute,
			"TIME"		=>	microtime(true) - $timestamp_absolute,
			"PARENTPID" =>	$parent_pid,
			"COMPLETED" =>	$completed_processes,
			"FAILED"	=>	$failed_processes,
			"CPUAVG"	=>	sys_getloadavg(),
			"MEM"		=>	memory_get_usage(true),
			"MEMPEAK"	=>	memory_get_peak_usage(true),
			"USER"		=>	get_current_user(),
			"NICENESS"	=>	pcntl_getpriority()
		);

        $content = serialize($data);

        $status = file_put_contents($statusfile, $content);

    }

}
