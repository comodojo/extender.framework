<?php namespace Comodojo\Extender;

/**
 * Version information class
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
    
    static private $cachefile = "extender.cache";
    
    static final public function set($data) {

        $cachefile = EXTENDER_CACHE_FOLDER.self::$cachefile;

        $serialized_data = serialize($data);

        $cache = file_put_contents($cachefile, $serialized_data);

        return $cache;

    }

    static final public function get() {

        $cachefile = EXTENDER_CACHE_FOLDER.self::$cachefile;

        if ( file_exists($cachefile) === false ) return false;

        $data = file_get_contents($cachefile);

        if ( $data === false ) return false;
        
        else return unserialize($data);

    }

    static final public function purge() {

        $cachefile = EXTENDER_CACHE_FOLDER.self::$cachefile;

        return unlink($cachefile);

    }

}
