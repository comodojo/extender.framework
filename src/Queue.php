<?php namespace Comodojo\Extender;

/**
 * Dump information about current queue
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

class Queue {

    /**
     * Standard dump file, will be placed in EXTENDER_CACHE_FOLDER
     *
     * @var     string
     */
    private static $queuefile = "extender.queue";

    /**
     * Dump live informations from extender process (if in daemon mode and pcntl installed)
     *
     * @param   int     $timestamp_absolute
     * @param   itn     $parent_pid
     * @param   int     $completed_processes
     * @param   itn     $failed_processes
     */
    final public static function dump($running, $queued) {

        $queuefile = EXTENDER_CACHE_FOLDER.self::$queuefile;

        $data = array(
            "RUNNING"   =>  $running,
            "QUEUED"    =>  $queued
        );

        $content = serialize($data);

        return file_put_contents($queuefile, $content);

    }

    /**
     * Remove the status file
     *
     * @return  bool
     */
    final public static function release() {

        $queuefile = EXTENDER_CACHE_FOLDER.self::$queuefile;

        if ( file_exists($queuefile) ) return unlink($queuefile);
        
        else return false;

    }

}
