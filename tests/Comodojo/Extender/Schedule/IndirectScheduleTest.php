<?php namespace Comodojo\Extender\Tests\Schedule;

use Comodojo\Extender\Tests\Base\AbstractIndirectTestCase;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Extender\Task\TaskParameters;
use \Comodojo\Extender\Orm\Entities\Schedule;
use \Cron\CronExpression;

class IndirectScheduleTest extends AbstractIndirectTestCase {

    protected static $schedule_name = "test_schedule";

    public function testAddSchedule() {

        $request = Request::create(
            'testchain',
            'test'
        )->pipe(
            Request::create(
                'testchainpipe',
                'test'
            )
        );

        $schedule = new Schedule();

        $expression = CronExpression::factory('* * * * *');

        $schedule
            ->setName(self::$schedule_name)
            ->setDescription('This is a test')
            ->setRequest($request)
            ->setExpression($expression);

        $data = $this->send('scheduler:add', $schedule);

        $this->assertGreaterThanOrEqual(1, $data);

    }

    public function testRemoveScheduleByName() {

        $data = $this->send('scheduler:removeByName', self::$schedule_name);

        $this->assertTrue($data);

    }

}
