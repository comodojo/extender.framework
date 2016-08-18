<?php namespace Comodojo\Extender\Utils;

use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Extender\Components\Database;
use \Exception;

/**
 * Framework wide checks
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

class Checks {

    /**
     * Check if script is running from command line
     *
     * @return  bool
     */
    final public static function cli() {

        return php_sapi_name() === 'cli';

    }

    /**
     * Check if php interpreter supports pcntl_fork (required in multithread mode)
     *
     * @return  bool
     */
    final public static function multithread() {

        return function_exists("pcntl_fork");

    }

    /**
     * Check if php interpreter supports pcntl signal handlers
     *
     * @return  bool
     */
    final public static function signals() {

        return function_exists("pcntl_signal");

    }

    /**
     * Check if database is available and initialized correctly
     *
     * @return  bool
     */
    final public static function database(Configuration $configuration) {

        try {

            $dbh = Database::init($configuration);

            $dbh->connect();

            $manager = $dbh->getSchemaManager();

            $manager->getTable($configuration->get('database-jobs-table'));
            $manager->getTable($configuration->get('database-worklogs-table'));
            $manager->getTable($configuration->get('database-queue-table'));

        } catch (Exception $e) {

            return false;

        } finally {

            $dhb->close();

        }

        return true;

    }

}
