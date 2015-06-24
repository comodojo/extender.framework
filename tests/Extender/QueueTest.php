<?php

class QueueTest extends \PHPUnit_Framework_TestCase {

    protected $running = 10;

    protected $queued = 50;

    public function testDump() {

        $result = \Comodojo\Extender\Queue::dump($this->running, $this->queued);

        $this->assertNotFalse($result);

        $this->assertGreaterThan(1, $result);

    }

    public function testRelease() {

        $result = \Comodojo\Extender\Queue::release();

        $this->assertTrue($result);

    }
    
}
