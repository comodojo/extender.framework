<?php namespace Comodojo\Extender\Components;

use \Psr\Log\LoggerInterface;
use \Exception;

/**
 * Lock file manager (static methods)
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

class Niceness {
    
    private $logger;
    
    private $niceness = 0;
    
    public function __contruct(LoggerInterface $logger) {
        
        $this->logger = $logger;
        
    } 

    public function get($pid = null) {
        
        return is_null($pid) ? $this->niceness : pcntl_getpriority($pid);
        
    }

    public function set($niceness = null, $pid = null) {
        
        $niceness = self::getNiceness($niceness);

        if ( is_null($pid) ) {
            
            $nice = proc_nice($niceness);

            if ( $nice === false ) {
                
                $this->logger->warning("Unable to set parent process niceness to $niceness");
            
            } else {
                
                $this->niceness = $niceness;
                
            }
        
        } else {
            
            $nice = pcntl_setpriority($pid, $$niceness);

            if ( $nice == false ) $logger->warning("Unable to set child process $pid niceness to $niceness");
            
        }
        
        return $nice;
        
    }
    
    private static function getNiceness($niceness=null) {
        
        return filter_var($niceness, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0,
                'min_range' => -10,
                'max_range' => 10
            )
        ));
        
    }

}
