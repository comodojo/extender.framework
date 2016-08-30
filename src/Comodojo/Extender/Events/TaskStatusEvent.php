<?php namespace Comodojo\Extender\Events;

use \Comodojo\Extender\Tasks\TaskInterface;

/**
 * @package     Comodojo Dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
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

class TaskStatusEvent extends AbstractEvent {

    private $status;

    private $task;

    public function __construct($status, TaskInterface $task) {

        $name = $task->name;

        parent::__construct("extender.task.$name.$status");

        $this->status = $status;

        $this->task = $task;

    }

    public function getStatus() {

        return $this->status;

    }

    public function getTask() {

        return $this->task;

    }

}