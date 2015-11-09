<?php namespace Comodojo\Extender\Command;

use \Monolog\Logger;
use \Console_Color2;
use \Comodojo\Extender\TasksTable;

/**
 * The CommandInterface, base interface that any command should implement
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

interface CommandInterface {

    /**
     * Set options
     *
     * @param   array   $options    Provided options (if any)
     *
     * @return  Object  $this
     */
    public function setOptions($options);

    /**
     * Set arguments
     *
     * @param   array   $args       Provided arguments (if any)
     *
     * @return  Object  $this
     */
    public function setArguments($args);

    /**
     * Inject Console_Color2 instance
     *
     * @param   Object  $color     The Console_Color2 instance
     *
     * @return  Object  $this
     */
    public function setColor(Console_Color2 $color);

    /**
     * Set registered tasks
     *
     * @param   \Comodojo\Extender\Task\TasksTable   $tasks     TaskTable
     *
     * @return  Object  $this
     */
    public function setTasks(TaskTable $tasks);

    /**
     * Set logger
     *
     * @param   \Monolog\Logger   $logger
     *
     * @return  \Comodojo\Extender\Command\AbstractCommand
     */
    public function setLogger(Logger $logger);

    /**
     * Get an option
     *
     * @param   string  $option    The option to search for
     *
     * @return  string
     */
    public function getOption($option);

    /**
     * Get an argument
     *
     * @param   string  $arg       The argument to search for
     *
     * @return  string
     */
    public function getArgument($arg);

    /**
     * Execute command
     *
     * @return  string
     */
    public function execute();

}
