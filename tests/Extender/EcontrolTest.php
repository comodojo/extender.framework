<?php

class EcontrolTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
        
        $this->extender = new \Comodojo\Extender\Econtrol();
    
    }

    protected function tearDown() {

        unset($this->extender);

    }

    public function testExtender() {

        $this->assertInstanceOf('\Comodojo\Extender\Econtrol', $this->extender);

    }

    public function testStartupConfiguration() {

    	$result = $this->extender->addTask('testTask', '\test\class', 'test task');

    	$this->assertTrue($result);

        $result = $this->extender->addCommand('testcommand', array (
            'description' => 'Test Command',
            'aliases' => array (),
            'options' => array (),
            'arguments' => array ()
        ));

        $this->assertTrue($result);

    }
    
}
