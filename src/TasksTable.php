<?php namespace Comodojo\Extender;

/**
 * Tasks table
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

class TasksTable {

    /**
     * Tasks database (a simple array!).
     *
     * @var     array
     */
    private $tasks = array();

    /**
     * Register a task
     *
     * @param   string    $name         Task name (unique)
     * @param   string    $class        The target class to invoke
     * @param   string    $description  A brief description for the task
     *
     * @return  bool
     */
    final public function addTask($name, $class, $description) {

        if ( empty($name) || empty($class) ) return false;

        $this->tasks[$name] = array(
            "description" => $description,
            "class"       => $class

        );

        return true;

    }

    /**
     * Delete a task from registry
     *
     * @param   string    $name         Task name (unique)
     *
     * @return  bool
     */
    final public function removeTask($name) {

        if ( $this->registered($name) ) {

            unset($this->tasks[$name]);

            return true;

        }

        else return false;

    }

    /**
     * Check if task is registered
     *
     * @param   string    $task    Task name (unique)
     *
     * @return  string
     */
    final public function isTaskRegistered($task) {

        return $this->registered($task);

    }

    /**
     * Get task description
     *
     * @param   string    $task    Task name (unique)
     *
     * @return  string
     */
    final public function getDescription($task) {

        if ( $this->registered($task) ) return $this->tasks[$task]["description"];

        else return null;

    }

    /**
     * Get task class
     *
     * @param   string    $task    Task name (unique)
     *
     * @return  string
     */
    final public function getClass($task) {

        if ( $this->registered($task) ) return $this->tasks[$task]["class"];

        else return null;

    }

    /**
     * Get whole table
     *
     * @return  array
     */
    final public function getTasks($sort=false) {
        
        if ( $sort === true ) ksort($this->tasks);

        return $this->tasks;

    }

    /**
     * Check if task is registered
     *
     * @param   string    $task    Task name (unique)
     *
     * @return  bool
     */
    private function registered($task) {

        if ( array_key_exists($task, $this->tasks) ) return true;

        else return false;

    }

}
