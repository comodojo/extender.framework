<?php namespace Comodojo\Extender\Components;

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

class ProcessUtils {
    
    /**
     * Kill a child process
     * 
     * @return  bool
     */
    public static function kill($pid, $lagger_timeout = 0) {

        $kill_time = time() + self::$lagger_timeout;

        $term = posix_kill($pid, SIGTERM);

        while ( time() < $kill_time ) {
            
            if ( !self::isRunning($pid) ) return $term;

        }

        return posix_kill($pid, SIGKILL);

    }
    
    /**
     * Return true if process is still running, false otherwise
     * 
     * @return  bool
     */
    public static function isRunning($pid) {

        return (pcntl_waitpid($pid, $status, WNOHANG) === 0);

    }
    
    public static function getNiceness($pid = null) {
        
        return pcntl_getpriority($pid);
        
    }
    
    public function setNiceness($niceness = null, $pid = null) {

        $niceness = self::filterNiceness($niceness);

        if ( is_null($pid) ) {

            return proc_nice($niceness);

        } 
        
        return pcntl_setpriority($pid, $$niceness);

    }

    public static function filterNiceness($niceness=null) {

        return filter_var($niceness, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => -10,
                'max_range' => 10
            )
        ));

    }
    
}