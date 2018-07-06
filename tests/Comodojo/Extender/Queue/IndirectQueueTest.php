<?php namespace Comodojo\Extender\Tests\Queue;

use \Comodojo\Extender\Tests\Base\AbstractIndirectTestCase;
use \Comodojo\RpcClient\RpcRequest;
use \Comodojo\Extender\Socket\Messages\Task\Request;

/**
 * @group indirect
 * @group queue
 */
class IndirectQueueTest extends AbstractIndirectTestCase {

    // protected $actual_pid;
    //
    protected $uid;
    //
    // public function testStartQueueInfo() {
    //
    //     $info = $this->send('queue:info');
    //
    //     $this->queueInfoParser($info);
    //
    //     $this->actual_pid = $info['USAGE']['PID'];
    //
    // }

    public function testSimpleTask() {

        $message = Request::create(
            'test',
            '\Comodojo\Extender\Tests\Mock\Task',
            ['copy'=>'this is a queue test']
        )->export();

        $data = $this->send(RpcRequest::create("queue.add", [$message]));

        $this->assertCount(128, str_split($data));
        $this->assertStringMatchesFormat('%s', $data);

        $this->uid = $data;

    }

    public function testTaskChain() {

        $message = Request::create(
            'test',
            '\Comodojo\Extender\Tests\Mock\Task'
        )->setMaxtime(5)
        ->setNiceness(2)
        ->onFail(
            Request::create(
                'test',
                '\Comodojo\Extender\Tests\Mock\Task'
            )
        )->export();

        $data = $this->send(RpcRequest::create("queue.add", [$message]));

        $this->assertCount(128, str_split($data));
        $this->assertStringMatchesFormat('%s', $data);

    }

    public function testBulkTaskChain() {

        $message = [
            Request::create(
                'test',
                '\Comodojo\Extender\Tests\Mock\Task'
            )->setMaxtime(5)
            ->setNiceness(2)
            ->onFail(
                Request::create(
                    'test',
                    '\Comodojo\Extender\Tests\Mock\Task'
                )
            )->export(),
            Request::create(
                'test',
                '\Comodojo\Extender\Tests\Mock\Task'
            )->pipe(
                Request::create(
                    'test',
                    '\Comodojo\Extender\Tests\Mock\Task'
                )->pipe(Request::create(
                    'test',
                    '\Comodojo\Extender\Tests\Mock\Task',
                    ['notice'=>true]
                ))
            )->onDone(
                Request::create(
                    'test',
                    '\Comodojo\Extender\Tests\Mock\Task'
                )->onDone(
                    Request::create(
                        'test',
                        '\Comodojo\Extender\Tests\Mock\Task'
                    )
                )
            )->onFail(
                Request::create(
                    'test',
                    '\Comodojo\Extender\Tests\Mock\Task'
                )
            )->export()
        ];

        $data = $this->send(RpcRequest::create("queue.addBulk", [$message]));

        $this->assertCount(2, $data);

        foreach ($data as $response) {
            $this->assertCount(128, str_split($response));
            $this->assertStringMatchesFormat('%s', $response);
        }

    }

    public function testEndQueueInfo() {

        $info = $this->send(RpcRequest::create("queue.info"));

        $this->queueInfoParser($info);

        // $this->assertEquals($this->actual_pid, $info['USAGE']['PID']);

    }

    public function testInvalidTask() {

        $message = Request::create(
            'testboo',
            '\Comodojo\Extender\Tests\Fock\Task'
        )->export();

        $data = $this->send(RpcRequest::create("queue.add", [$message]));

        $this->assertCount(128, str_split($data));
        $this->assertStringMatchesFormat('%s', $data);

    }

    // /**
    //  * @afterClass
    //  */
    // public function testWorklogRetrieval() {
    //
    //     var_dump($this->uid);
    //
    //     $info = $this->send('worklog:getByUid', $this->uid);
    //
    //     var_dump($info);
    //
    // }

    protected function queueInfoParser($data) {

        $this->assertCount(2, $data);

        $this->assertArrayHasKey('USAGE', $data);
        $this->assertArrayHasKey('COUNTERS', $data);

    }

}
