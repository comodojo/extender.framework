<?php namespace Comodojo\Extender;

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

class Lock {

    /**
     * Lock file name
     *
     * @var     string
     */
    static private $lockfile = "extender.pid";
    
    /**
     * Register pid into lock file
     *
     * @param   string     $pid    Pid to register
     *
     * @return  bool
     */
    static final public function register($pid) {

        if ( empty($pid) ) throw new Exception("Invalid pid reference");

        $lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;

        $lock = file_put_contents($lockfile, $pid);

        if ( $lock === false ) throw new Exception("Cannot write lock file");

        return $lock;

    }

    /**
     * Remove the lock file
     *
     * @return  bool
     */
    static final public function release() {

        $lockfile = EXTENDER_CACHE_FOLDER.self::$lockfile;
        
        $lock = unlink($lockfile);

        return $lock;

    }

}
