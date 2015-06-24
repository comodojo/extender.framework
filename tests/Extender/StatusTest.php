<?php

class StatusTest extends \PHPUnit_Framework_TestCase {

    protected $timestamp_absolute = 1435179611;

    protected $parent_pid = 12345;

    protected $completed_processes = 1;

    protected $failed_processes = 0;

    protected $paused = false;

    public function testDump() {

        $result = \Comodojo\Extender\Status::dump(
            $this->timestamp_absolute,
            $this->parent_pid,
            $this->completed_processes,
            $this->failed_processes,
            $this->paused
        );

        $this->assertNotFalse($result);

        $this->assertGreaterThan(1, $result);

    }

    public function testRelease() {

        $result = \Comodojo\Extender\Status::release();

        $this->assertTrue($result);

    }
    
}
