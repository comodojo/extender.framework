<?php namespace Comodojo\Extender;

/**
 * Version information class
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

class Version {
    
    /**
     * Extender brief description
     *
     * @var     string
     */
    private static $description = "Daemonizable, database driven, multiprocess, (pseudo) cron task scheduler";

    /**
     * Extender current version
     *
     * @var     string
     */
    private static $version = "1.0.0-beta";

    /**
     * Get extender framework description
     *
     * @return  string
     */
    final static public function getDescription() {

        $description = (defined("EXTENDER_CUSTOM_DESCRIPTION") AND is_string(EXTENDER_CUSTOM_DESCRIPTION)) ? EXTENDER_CUSTOM_DESCRIPTION : self::$description;

        $ascii = (defined("EXTENDER_CUSTOM_ASCII") AND is_readable(EXTENDER_CUSTOM_ASCII)) ? file_get_contents(EXTENDER_CUSTOM_ASCII) : self::ascii();

        return $ascii."\n".$description."\n";

    }

    /**
     * Get extender framework version
     *
     * @return  string
     */
    final static public function getVersion() {

        return (defined("EXTENDER_CUSTOM_VERSION") AND is_string(EXTENDER_CUSTOM_VERSION)) ? EXTENDER_CUSTOM_VERSION : self::$version;

    }

    /**
     * Get fancy extender logo
     *
     * @return  string
     */
    private static function ascii() {

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
        $ascii .= "--------------------------------------------------------         \r\n";
        
        return $ascii;

    }

}
