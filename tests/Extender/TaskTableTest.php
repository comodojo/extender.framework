<?php

class TaskTableTest extends \PHPUnit_Framework_TestCase {

    protected $task = null;

    private $name = "TestTask";

    private $class = "\\Comodojo\\Extender\\Task\\TestTask";

    private $description = "TestTask description";

    protected function setUp() {
        
        $this->task = new \Comodojo\Extender\TasksTable();
    
    }

    protected function tearDown() {

        unset($this->job);

    }

    /**
     * @before
     */
    public function testAddTask() {

        $result = $this->task->addTask($this->name, $this->class, $this->description);

        $this->assertTrue($result);

    }

    public function testRemoveTask() {

        $result = $this->task->removeTask($this->name);

        $this->assertTrue($result);

        $result = $this->task->removeTask($this->name);        

        $this->assertFalse($result);

    }

    public function testIsTaskRegistered() {

        $result = $this->task->isTaskRegistered($this->name);

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
