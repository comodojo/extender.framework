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

class Version {
    
    /**
     * Extender brief description
     *
     * @var     string
     */
    static private $description = "Daemonizable, database driven, multiprocess, (pseudo) cron task scheduler in PHP";

    /**
     * Extender current version
     *
     * @var     string
     */
    static private $version = "1.0.0-beta2";

    /**
     * Get extender framework description
     *
     * @return  string
     */
    static final public function getDescription() {

        return self::ascii()."\n".self::$description."\n";

    }

    /**
     * Get extender framework version
     *
     * @return  string
     */
    static final public function getVersion() {

        return self::$version;

    }

    /**
     * Get fancy extender logo
     *
     * @return  string
     */
    static private function ascii() {

        $ascii = "\n   ______                                __            __        \r\n";
        $ascii .= "  / ____/ ____    ____ ___   ____   ____/ / ____      / /  ____  \r\n";
        $ascii .= " / /     / __ \  / __ `__ \ / __ \ / __  / / __ \    / /  / __ \ \r\n";
        $ascii .= "/ /___  / /_/ / / / / / / // /_/ // /_/ / / /_/ /   / /  / /_/ / \r\n";
        $ascii .= "\____/  \____/ /_/ /_/ /_/ \____/ \__,_/  \____/  _/ /   \____/  \r\n";
        $ascii .= "----------------------------------------------  /___/  --------- \r\n";
        $ascii .= "                 __                      __                      \r\n";
        $ascii .= "  ___    _  __  / /_  ___    ____   ____/ / ___    _____         \r\n";
        $ascii .= " / _ \  | |/_/ / __/ / _ \  / __ \ / __  / / _ \  / ___/         \r\n";
        $ascii .= "/  __/ _>  <  / /_  /  __/ / / / // /_/ / /  __/ / /             \r\n";
        $ascii .= "\___/ /_/|_|  \__/  \___/ /_/ /_/ \__,_/  \___/ /_/              \r\n";
        $ascii .= "--------------------------------------------------------         \r\n\n";
        
        return $ascii;

    }

}
