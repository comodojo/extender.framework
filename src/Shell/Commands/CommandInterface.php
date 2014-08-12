<?php namespace Comodojo\Extender\Shell\Commands;

interface CommandInterface {

	public function setOptions($options);

	public function setArgs($args);

	public function setColor($color);

	public function setTasks($tasks);

	public function getOption($option);

	public function getArg($arg);

	public function exec();

}