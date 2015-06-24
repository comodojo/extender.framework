<?php

class JobTest extends \PHPUnit_Framework_TestCase {

    protected $job = null;

    protected function setUp() {
        
        $this->job = new \Comodojo\Extender\Job\Job();
    
    }

    protected function tearDown() {

        unset($this->job);

    }

    public function testName() {

        $result = $this->job->setName('test');

        $this->assertInstanceOf('\Comodojo\Extender\Job\Job', $result);

        $result = $this->job->getName();

        $this->assertEquals('test', $result);

    }

    public function testId() {

        $result = $this->job->setId(1);

        $this->assertInstanceOf('\Comodojo\Extender\Job\Job', $result);

        $result = $this->job->getId();

        $this->assertEquals(1, $result);

    }

    public function testParameters() {

        $result = $this->job->setParameters(array("foo"=>"boo"));

        $this->assertInstanceOf('\Comodojo\Extender\Job\Job', $result);

        $result = $this->job->getParameters();

        $this->assertEquals(array("foo"=>"boo"), $result);

    }

    public function testSetTask() {

        $result = $this->job->setTask('task');

        $this->assertInstanceOf('\Comodojo\Extender\Job\Job', $result);

        $result = $this->job->getTask();

        $this->assertEquals('task', $result);

    }

    public function testSetTarget() {

        $result = $this->job->setTarget('task.php');

        $this->assertInstanceOf('\Comodojo\Extender\Job\Job', $result);

        $result = $this->job->getTarget();

        $this->assertEquals('task.php', $result);

    }

    public function testSetClass() {

        $result = $this->job->setClass('TestClass');

        $this->assertInstanceOf('\Comodojo\Extender\Job\Job', $result);

        $result = $this->job->getClass();

        $this->assertEquals('TestClass', $result);

    }
    
}
