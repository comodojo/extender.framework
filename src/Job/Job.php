<?php namespace Comodojo\Extender\Job;

/**
 * Job object
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

class Job {

    /**
     * Job name
     *
     * @var     string
     */
    private $name = null;

    /**
     * Job id
     *
     * @var     int
     */
    private $id = null;

    /**
     * Job parameters
     *
     * @var     array
     */
    private $parameters = array();

    /**
     * Relative task
     *
     * @var     string
     */
    private $task = null;

    /**
     * Job target
     *
     * @var     string
     */
    private $target = null;

    /**
     * Job class
     *
     * @var     string
     */
    private $class = null;

    /**
     * Set job name
     *
     * @param   string  $name   The job name
     *
     * @return  Object  $this
     */
    final public function setName($name) {

        $this->name = $name;

        return $this;

    }

    /**
     * Get job name
     *
     * @return  string
     */
    final public function getName() {
        
        return $this->name;

    }

    /**
     * Set job id
     *
     * @param   string  $id     The job id
     *
     * @return  Object  $this
     */
    final public function setId($id) {

        $this->id = $id;

        return $this;

    }

    /**
     * Get job id
     *
     * @return  int
     */
    final public function getId() {

        return $this->id;
        
    }

    /**
     * Set job parameters
     *
     * @param   array   $parameters     Provided job parameters
     *
     * @return  Object  $this
     */
    final public function setParameters($parameters) {

        $this->parameters = is_array($parameters) ? $parameters : array();

        return $this;

    }

    /**
     * Get job parameters
     *
     * @return  array
     */
    final public function getParameters() {
        
        return $this->parameters;

    }

    /**
     * Set job associated task
     *
     * @param   string  $task   The task name
     *
     * @return  Object  $this
     */
    final public function setTask($task) {

        $this->task = $task;

        return $this;

    }

    /**
     * Get job task
     *
     * @return  string
     */
    final public function getTask() {
        
        return $this->task;

    }

    /**
     * Set job target
     *
     * @param   string  $target     The job target
     *
     * @return  Object  $this
     */
    final public function setTarget($target) {

        $this->target = $target;

        return $this;

    }

    /**
     * Get job target
     *
     * @return  string
     */
    final public function getTarget() {
        
        return $this->target;

    }

    /**
     * Set job class
     *
     * @param   string  $class   The job class
     *
     * @return  Object  $this
     */
    final public function setClass($class) {

        $this->class = $class;

        return $this;

    }

    /**
     * Get job class
     *
     * @return  string
     */
    final public function getClass() {
        
        return $this->class;

    }
    
}
