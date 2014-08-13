<?php namespace Comodojo\Extender\Task;

/**
 * Tasks table
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
     * @param   string    $target       Target task file
     * @param   string    $description  A brief description for the task
     * @param   string    $class        (optional) Task class, if different from file name
     * @param   bool      $relative     (optional) If relative, a task will be loaded in EXTENDER_TASK_FOLDER
     *
     * @return  bool
     */
    final public function addTask($name, $target, $description, $class=null, $relative=true) {

        if ( empty($name) OR empty($target) ) return false;

        $this->tasks[$name] = array(
            "description" => $description,
            "target"      => $relative ? EXTENDER_TASK_FOLDER.$target : $target,
            "class"       => empty($class) ? preg_replace('/\\.[^.\\s]{3,4}$/', '', $target) : $class
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

        if ( $this->registered($task) ) {

            unset($this->tasks[$task]);

            return true;

        }

        else return false;

    }

    /**
     * Check if task is registered
     *
     * @param   string    $name         Task name (unique)
     *
     * @return  string
     */
    final public function isTaskRegistered($task) {

        return $this->registered($task);

    }

    /**
     * Get task description
     *
     * @param   string    $name         Task name (unique)
     *
     * @return  string
     */
    final public function getDescription($task) {

        if ( $this->registered($task) ) return $this->tasks[$task]["description"];

        else return null;

    }

    /**
     * Get task target
     *
     * @param   string    $name         Task name (unique)
     *
     * @return  string
     */
    final public function getTarget($task) {

        if ( $this->registered($task) ) return $this->tasks[$task]["target"];

        else return null;

    }

    /**
     * Get task class
     *
     * @param   string    $name         Task name (unique)
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
    final public function getTasks() {

        return $this->tasks;

    }

    /**
     * Check if task is registered
     *
     * @param   string    $name         Task name (unique)
     *
     * @return  bool
     */
    private function registered($task) {

        if ( array_key_exists($task, $this->tasks) ) return true;

        else return false;

    }

}
