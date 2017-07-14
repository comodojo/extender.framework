<?php namespace Comodojo\Extender\Traits;

use \Comodojo\Extender\Task\Table as TasksTable;

trait TasksTableTrait {

    /**
     * @var TasksTable
     */
    protected $table;

    /**
     * Get current TasksTable
     *
     * @return TasksTable
     */
    public function getTasksTable() {

        return $this->table;

    }

    /**
     * Set current TasksTable
     *
     * @param TasksTable $table
     * @return self
     */
    public function setTasksTable(TasksTable $table) {

        $this->table = $table;

        return $this;

    }

}
