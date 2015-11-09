<?php

use \Comodojo\Extender\Log\ExtenderLogger;

class TaskTableTest extends \PHPUnit_Framework_TestCase {

    protected $task = null;

    private $name = "TestTask";

    private $class = "\\Comodojo\\Extender\\Task\\TestTask";

    private $description = "TestTask description";

    protected function setUp() {

        $debug = ExtenderLogger::create(false);
        
        $this->task = new \Comodojo\Extender\TasksTable($debug);
    
    }

    protected function tearDown() {

        unset($this->job);

    }

    /**
     * @before
     */
    public function testAddTask() {

        $result = $this->task->add($this->name, $this->class, $this->description);

        $this->assertTrue($result);

    }

    public function testRemoveTask() {

        $result = $this->task->remove($this->name);

        $this->assertTrue($result);

        $result = $this->task->remove($this->name);        

        $this->assertFalse($result);

    }

    public function testIsTaskRegistered() {

        $result = $this->task->isRegistered($this->name);

        $this->assertTrue($result);

    }

    public function testGetDescription() {

        $result = $this->task->getDescription($this->name);

        $this->assertSame($this->description, $result);

    }

    public function testGetClass() {

        $result = $this->task->getClass($this->name);

        $this->assertSame($this->class, $result);

    }

    public function testGetTasks() {

        $result = $this->task->getTasks();

        $this->assertInternalType('array', $result);

        $this->assertCount(1, $result);

        $this->assertArrayHasKey($this->name, $result);

    }

}
