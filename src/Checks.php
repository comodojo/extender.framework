<?php namespace Comodojo\Extender;

/**
 * Do checks
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

class Checks {
    
    static final public function constants() {

        if ( !defined("EXTENDER_DATABASE_MODEL") ) return "Invalid database model. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_MODEL.";
        if ( !defined("EXTENDER_DATABASE_HOST") ) return "Unknown database host. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_HOST.";
        if ( !defined("EXTENDER_DATABASE_PORT") ) return "Invalid database port. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_PORT.";
        if ( !defined("EXTENDER_DATABASE_NAME") ) return "Invalid database name. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_NAME.";
        if ( !defined("EXTENDER_DATABASE_USER") ) return "Invalid database user. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_USER.";
        if ( !defined("EXTENDER_DATABASE_PASS") ) return "Invalid database password. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_PASS.";
        if ( !defined("EXTENDER_DATABASE_PREFIX") ) return "Invalid database table prefix. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_PREFIX.";
        if ( !defined("EXTENDER_DATABASE_TABLE_JOBS") ) return "Invalid database jobs' table. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_TABLE_JOBS.";
        if ( !defined("EXTENDER_DATABASE_TABLE_WORKLOGS") ) return "Invalid database worklogs' table. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_TABLE_WORKLOGS.";
        if ( !defined("EXTENDER_TASK_FOLDER") ) return "Invalid tasks folder. \n\n Please check your extender configuration and define constant: EXTENDER_TASK_FOLDER.";
        if ( !defined("EXTENDER_CACHE_FOLDER") ) return "Invalid cache folder. \n\n Please check your extender configuration and define constant: EXTENDER_CACHE_FOLDER.";
        //if ( !defined("EXTENDER_CACHE_TTL") ) return "Invalid cache folder. \n\n Please check your extender configuration and define constant: EXTENDER_CACHE_FOLDER.";
        
        return true;

    }

    static final public function cli() {

        return php_sapi_name() === 'cli';

    }

    static final public function multithread() {

        return function_exists("pcntl_fork");

    }

}