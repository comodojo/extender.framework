<?php namespace Comodojo\Extender\Command;

/**
 * Common superclass for extender default commands
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

abstract class AbstractCommand implements CommandInterface {

    /**
     * Array of commandline options
     *
     * @var array
     */
    protected $options = null;

    /**
     * Array of commandline arguments
     *
     * @var array
     */
    protected $args = null;

    /**
     * Internal pointer to ConsoleColor object
     *
     * @var object
     */
    protected $color = null;

    /**
     * Array of registered tasks
     *
     * @var array
     */
    protected $tasks = array();

    /**
     * Set options
     *
     * @param   array   $options    Provided options (if any)
     *
     * @return  Object  $this
     */
    public function setOptions($options) {

        $this->options = $options;

        return $this;

    }

    /**
     * Set arguments
     *
     * @param   array   $args       Provided arguments (if any)
     *
     * @return  Object  $this
     */
    public function setArguments($args) {

        $this->args = $args;

        return $this;

    }

    /**
     * Inject Console_Color2 instance
     *
     * @param   Object  $color     The Console_Color2 instance
     *
     * @return  Object  $this
     */
    public function setColor($color) {

        $this->color = $color;

        return $this;

    }

    /**
     * Set registered tasks
     *
     * @param   \Comodojo\Extender\Task\TasksTable   $tasks     TaskTable
     *
     * @return  Object  $this
     */
    public function setTasks($tasks) {

        $this->tasks = $tasks;

        return $this;

    }

    /**
     * Get an option
     *
     * @param   string  $option    The option to search for
     *
     * @return  string
     */
    public function getOption($option) {

        if ( array_key_exists($option, $this->options) ) return $this->options[$option];

        else return null;

    }

    /**
     * Get an argument
     *
     * @param   string  $arg       The argument to search for
     *
     * @return  string
     */
    public function getArgument($arg) {

        if ( array_key_exists($arg, $this->args) ) return $this->args[$arg];

        else return null;

    }

    /**
     * The execute method; SHOULD be implemented by each command
     *
     * @return  string
     */
    abstract public function execute();

}
