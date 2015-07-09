<?php namespace Comodojo\Extender\Scheduler;

/**
 * Scheduler table (Schedule)
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

class Schedule {

    /**
     * Tasks database (a simple array!).
     *
     * @var     array
     */
    private $schedules = array();

    /**
     * Override whole jobs list (schedule)
     *
     * @param   array     $schedules
     *
     * @return  Object    $this
     */
    final public function setSchedules(array $schedules) {

        $this->schedules = $schedules;

        return $this;

    }

    /**
     * Register a schedule (job)
     *
     * @param   string    $name         Job name (unique)
     * @param   string    $task         Target task
     * @param   string    $expression   A valid cron expression
     * @param   string    $description  (optional) A brief description for the job
     * @param   bool      $parameters   (optional) array of parameters to pass to task
     *
     * @return  bool
     */
    final public function addSchedule($name, $task, $expression, $description=null, $parameters=array()) {

        if ( empty($name) OR empty($task) OR empty($expression) OR Scheduler::validateExpression($expression) ) return false;

        list($min, $hour, $dayofmonth, $month, $dayofweek, $year) = explode(" ", trim($expression));

        array_push($this->schedules, array(
            "name" => $name,
            "task" => $task,
            "description" => is_null($description) ? '' : $description,
            "min" => $min,
            "hour" => $hour,
            "dayofmonth" => $dayofmonth,
            "month" => $month,
            "dayofweek" => $dayofweek,
            "year" => $year,
            "params" => $parameters
        ));

        return true;

    }

    /**
     * Delete a task from registry
     *
     * @param   string    $name         Task name (unique)
     *
     * @return  bool
     */
    final public function getSchedules() {

        return $this->schedules;

    }

    /**
     * Check if job is scheduled
     *
     * @param   string    $job          Job name (unique)
     *
     * @return  bool
     */
    final public function isScheduled($job) {

        if ( empty($job) ) return false;

        foreach ($this->schedules as $schedule) {

            if ( $schedule['name'] = $job ) return true;
            
        }

        return false;

    }

    /**
     * Get job schedule details
     *
     * @param   string    $job          Job name (unique)
     *
     * @return  array
     */
    final public function getSchedule($job) {

        if ( empty($job) ) return null;

        foreach ($this->schedules as $schedule) {

            if ( $schedule['name'] = $job ) return $schedule;
            
        }

        return null;

    }

    /**
     * Get number or jobs to be executed
     *
     * @return  integer
     */
    final public function howMany() {

        return sizeof($this->schedules);

    }

}
