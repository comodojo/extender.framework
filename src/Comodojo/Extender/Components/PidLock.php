<?php namespace Comodojo\Extender\Components;

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

class PidLock {

    /**
     * Lock file name
     *
     * @var string
     */
    private $lockfile = "extender.pid";
    
    private $pid;
    
    public function __construct($pid, $lockfile = null) {
        
        if ( $lockfile !== null ) $this->lockfile = $lockfile;
        
        if ( empty($pid) ) throw new Exception("Invalid pid reference");
        
        $this->pid = $pid;
        
    }

    /**
     * Register pid into lock file
     *
     * @param   string     $pid    Pid to register
     *
     * @return  bool
     */
    public function lock() {

        $lock = file_put_contents($this->lockfile, $pid);

        if ( $lock === false ) throw new Exception("Cannot write lock file");

        return $lock;

    }

    /**
     * Remove the lock file
     *
     * @return  bool
     */
    public static function release() {

        $lock = unlink($this->lockfile);

        return $lock;

    }

}
