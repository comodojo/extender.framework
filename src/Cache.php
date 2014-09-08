<?php namespace Comodojo\Extender;

/**
 * Small cache utility class, just to cache jobs' table
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

class Cache {
    
    /**
     * Standard cache file, will be placed in EXTENDER_CACHE_FOLDER
     *
     * @var     string
     */
    static private $cachefile = "extender.cache";
    
    /**
     * Save jobs info into cache file
     *
     * @param   array   $data
     *
     * @return  bool
     */
    static final public function set($data) {

        $cachefile = EXTENDER_CACHE_FOLDER.self::$cachefile;

        $serialized_data = serialize($data);

        $cache = file_put_contents($cachefile, $serialized_data);

        return $cache;

    }

    /**
     * Get jobs info from cache file
     *
     * @return  mixed   Array if cache not empty, false otherwise
     */
    static final public function get() {

        $cachefile = EXTENDER_CACHE_FOLDER.self::$cachefile;

        if ( file_exists($cachefile) === false ) return false;

        $data = file_get_contents($cachefile);

        if ( $data === false ) return false;
        
        else return unserialize($data);

    }

    /**
     * Purge cache (remove cache file)
     *
     * @return  bool
     */
    static final public function purge() {

        $cachefile = EXTENDER_CACHE_FOLDER.self::$cachefile;

        if ( file_exists($cachefile) ) return unlink($cachefile);
        
        return true;

    }

}
