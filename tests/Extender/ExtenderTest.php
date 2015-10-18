<?php

class ExtenderTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
        
        $this->extender = new \Comodojo\Extender\Extender();
    
    }

    protected function tearDown() {

        unset($this->extender);

    }

    public function testExtender() {

        $this->assertInstanceOf('\Comodojo\Extender\Extender', $this->extender);

    }

    public function testSettersAndGetters() {

    	// max result length

    	$result = $this->extender->setMaxResultLength(1024);

    	$this->assertInstanceOf('\Comodojo\Extender\Extender', $result);

    	$result = $this->extender->getMaxResultLength();

    	$this->assertEquals(1024, $result);

    	// max child runtime

    	$result = $this->extender->setMaxChildsRuntime(600);

    	$this->assertInstanceOf('\Comodojo\Extender\Extender', $result);

    	$result = $this->extender->getMaxChildsRuntime();

    	$this->assertEquals(600, $result);

    	// multithread mode

    	$result = $this->extender->setMultithreadMode(false);

    	$this->assertInstanceOf('\Comodojo\Extender\Extender', $result);

    	$result = $this->extender->getMultithreadMode();

    	$this->assertFalse($result);

    	// daemon mode

    	$result = $this->extender->getDaemonMode();

    	$this->assertFalse($result);

    	// processes

    	$result = $this->extender->getCompletedProcesses();

    	$this->assertEquals(0, $result);

    	$result = $this->extender->getFailedProcesses();

    	$this->assertEquals(0, $result);

    	// version

    	$result = $this->extender->getVersion();

    	$this->assertEquals('1.0.0-beta', $result);

    	// components

    	$result = $this->extender->getEvents();

    	$this->assertInstanceOf('\Comodojo\Extender\Events', $result);

    	$result = $this->extender->getColor();

    	$this->assertInstanceOf('\Console_Color2', $result);

    	$result = $this->extender->getDebugger();

    	$this->assertInstanceOf('\Comodojo\Extender\Debug', $result);

    	$result = $this->extender->getJobsRunner();

    	$this->assertInstanceOf('\Comodojo\Extender\Runner\JobsRunner', $result);

    	$result = $this->extender->getTasksTable();

    	$this->assertInstanceOf('\Comodojo\Extender\TasksTable', $result);

    }

    public function testStartupConfiguration() {

    	$result = $this->extender->addHook('extender', function($extender) { return true; });

    	$this->assertTrue($result);

    	$result = $this->extender->addTask('testTask', '\test\class', 'test task');

    	$this->assertTrue($result);

    }

    public function testExtend() {

    	$result = $this->extender->extend();

    	$this->assertNull($result);

    }
    
}
