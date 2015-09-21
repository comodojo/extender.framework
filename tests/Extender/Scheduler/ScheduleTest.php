<?php

class ScheduleTest extends \PHPUnit_Framework_TestCase {

    public function testSchedule() {

        $schedule = new \Comodojo\Extender\Scheduler\Schedule();

        $result = $schedule->addSchedule('test', 'testTask', "10 * * * * *", 'test schedule', array('this'=>'foo'));

        $this->assertTrue($result);

        $result = $schedule->getSchedules();

        $this->assertCount(1,$result);

        $result = $schedule->isScheduled('test');

        $this->assertTrue($result);

        $result = $schedule->isScheduled('test2');

        $this->assertFalse($result);

        $result = $schedule->getSchedule('test');

        $this->assertInternalType('array', $result);

    }

    public function testScheduleLoad() {

        $scheduled = array(
            array(
                "name" => 'test',
                "task" => 'testTask',
                "description" => null,
                "min" => '*',
                "hour" => '*',
                "dayofmonth" => '*',
                "month" => '*',
                "dayofweek" => '*',
                "year" => '*',
                "params" => array()
            )
        );

        $schedule = new \Comodojo\Extender\Scheduler\Schedule();

        $result = $schedule->setSchedules($scheduled);

        $this->assertInstanceOf('\Comodojo\Extender\Scheduler\Schedule', $result);

        $result = $schedule->howMany();

        $this->assertEquals(1,$result);

    }
    
}
