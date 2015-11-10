<?php namespace Comodojo\Extender\Command;

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
     * @return  \Comodojo\Extender\Command\CommandInterface
     */
    public function setOptions($options);

    /**
     * Set arguments
     *
     * @param   array   $args       Provided arguments (if any)
     *
     * @return  \Comodojo\Extender\Command\CommandInterface
     */
    public function setArguments($args);

    /**
     * Inject Console_Color2 instance
     *
     * @param   \Console_Color2
     *
     * @return  \Comodojo\Extender\Command\CommandInterface
     */
    public function setColor(\Console_Color2 $color);

    /**
     * Set registered tasks
     *
     * @param   \Comodojo\Extender\TasksTable
     *
     * @return  \Comodojo\Extender\Command\CommandInterface
     */
    public function setTasks(\Comodojo\Extender\TasksTable $tasks);

    /**
     * Set logger
     *
     * @param   \Monolog\Logger
     *
     * @return  \Comodojo\Extender\Command\CommandInterface
     */
    public function setLogger(\Monolog\Logger $logger);

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
