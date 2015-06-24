<?php

class LockTest extends \PHPUnit_Framework_TestCase {

    protected $pid = 12345;

    public function testRegister() {

        $result = \Comodojo\Extender\Lock::register($this->pid);

        $this->assertNotFalse($result);

        $this->assertGreaterThan(1, $result);

    }

    public function testRelease() {

        $result = \Comodojo\Extender\Lock::release();

        $this->assertTrue($result);

    }
    
}
