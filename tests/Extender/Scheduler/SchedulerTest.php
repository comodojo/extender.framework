<?php

use \Comodojo\Extender\Scheduler\Scheduler;

class SchedulerTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {

        $this->color = new \Console_Color2();

        $this->debug = new \Comodojo\Extender\Debug(false, $this->color);

    }

    protected function tearDown() {

        $this->color = null;

        $this->debug = null;

    }

    public function testScheduler() {

        $result = Scheduler::addSchedule("* * * * *", "test", "task", "description", array());

        $this->assertInternalType('array', $result);

        $this->assertInternalType('integer', $result[0]);

        $get = $result[0];

        $result = Scheduler::getSchedule("test");

        $this->assertInternalType('array', $result);

        $this->assertEquals($get, $result["id"]);

        $time = time();

        $result = Scheduler::updateSchedule("test", $time);

        $this->assertNull($result);

        $result = Scheduler::getSchedule("test");

        $this->assertEquals($time, $result["lastrun"]);

        $result = Scheduler::enableSchedule("test");

        $this->assertTrue($result);

        $result = Scheduler::disableSchedule("test");

        $this->assertTrue($result);

        $result = Scheduler::removeSchedule("test");

        $this->assertTrue($result);

    }
    
}
