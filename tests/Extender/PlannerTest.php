<?php

class PlannerTest extends \PHPUnit_Framework_TestCase {

    protected $planner_data = 1435179611;

    public function testSet() {

        $result = \Comodojo\Extender\Planner::set($this->planner_data);

        $this->assertNotFalse($result);

        $this->assertGreaterThan(1, $result);

    }

    public function testGet() {

        $result = \Comodojo\Extender\Planner::get();

        $this->assertSame($this->planner_data, $result);

    }

    public function testRelease() {

        $result = \Comodojo\Extender\Planner::release();

        $this->assertTrue($result);

    }
    
}
