<?php namespace Comodojo\Extender\Job;

class Job {

	private $name = null;

	private $id = null;

	private $parameters = array();

	private $task = null;

	private $class = null;

	final public function setName($name) {

		$this->name = $name;

		return $this;

	}

	final public function getName() {
		
		return $this->name;

	}

	final public function setId($id) {

		$this->id = $id;

		return $this;

	}

	final public function getId() {

		return $this->id;
		
	}

	final public function setParameters($parameters) {

		$this->parameters = is_array($parameters) ? $parameters : array();

		return $this;

	}

	final public function getParameters() {
		
		return $this->parameters;

	}

	final public function setTask($task) {

		$this->task = $task;

		return $this;

	}

	final public function getTask() {
		
		return $this->task;

	}

	final public function setTarget($target) {

		$this->target = $target;

		return $this;

	}

	final public function getTarget() {
		
		return $this->target;

	}

	final public function setClass($class) {

		$this->class = $class;

		return $this;

	}

	final public function getClass() {
		
		return $this->class;

	}
	
}