<?php namespace Comodojo\Extender\Tests\Schedule;

use Comodojo\Extender\Tests\Base\AbstractIndirectTestCase;
use \Comodojo\RpcClient\RpcRequest;
use \Comodojo\Extender\Socket\Messages\Task\Request;
use \Comodojo\Extender\Socket\Messages\Scheduler\Schedule;

class IndirectScheduleTest extends AbstractIndirectTestCase {

    protected static $schedule_name = "test_schedule";
    protected static $schedule_description = "Example Schedule";
    protected static $schedule_expression = "* * * * *";
    protected static $schedule_update_description = "TEST TEST";

    public function testSchedule() {

        // cleanup all schedules
        $schedules = $this->send(RpcRequest::create('scheduler.list'));
        foreach ($schedules as $schedule) {
            $this->assertTrue(
                $this->send(RpcRequest::create('scheduler.remove', [$schedule['id']]))
            );
        }

        // create new schedule
        $schedule = new Schedule();
        $schedule->setName(static::$schedule_name)
            ->setDescription(static::$schedule_description)
            ->setExpression(static::$schedule_expression)
            ->setEnabled(true);

        $request = Request::create(
            'testchain',
            'test'
        )->pipe(
            Request::create(
                'testchainpipe',
                'test'
            )
        );

        $schedule_id = $this->send(RpcRequest::create("scheduler.add", [
            $schedule->export(),
            $request->export()
        ]));

        $this->assertGreaterThanOrEqual(1, $schedule_id);

        // now get the schedule
        $data = $this->send(RpcRequest::create('scheduler.get', [$schedule_id]));
        $schedule = Schedule::createFromExport($data[0]);
        $request = Request::createFromExport($data[1]);

        // motify it
        $schedule->setDescription(self::$schedule_update_description);

        // update it
        $data = $this->send(RpcRequest::create('scheduler.edit', [
            $schedule->export(),
            $request->export()
        ]));
        $this->assertTrue($data);

        // double check edit step
        $data = $this->send(RpcRequest::create('scheduler.get', [$schedule_id]));
        $this->assertEquals(self::$schedule_update_description, $data[0]['description']);

        // enable schedule
        $this->assertTrue(
            $this->send(RpcRequest::create('scheduler.enable', [$schedule_id]))
        );

        // disable schedule (by name)
        $this->assertTrue(
            $this->send(RpcRequest::create('scheduler.disable', [$schedule->getName()]))
        );

        // remove schedule (by name)
        $this->assertTrue(
            $this->send(RpcRequest::create('scheduler.remove', [$schedule->getName()]))
        );

    }

}
