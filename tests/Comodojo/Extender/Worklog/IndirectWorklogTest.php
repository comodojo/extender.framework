<?php namespace Comodojo\Extender\Tests\Worklog;

use Comodojo\Extender\Tests\Base\AbstractIndirectTestCase;
use \Comodojo\RpcClient\RpcRequest;
use \Comodojo\Extender\Socket\Messages\Worklog\Filter;

class IndirectWorklogTest extends AbstractIndirectTestCase {

    public function testWorklogCount() {

        $info = $this->send(RpcRequest::create("worklog.count"));

        $this->assertGreaterThan(1, $info);

    }

    public function testWorklogListMethods() {

        $info = $this->send(RpcRequest::create("worklog.list"));

        $this->assertInternalType('array', $info);
        $this->assertCount(10, $info);

        $filter = Filter::create()->setLimit(11);

        $info = $this->send(RpcRequest::create("worklog.list", [$filter->export()]));

        $this->assertInternalType('array', $info);
        $this->assertCount(11, $info);

        $uid = $info[5]['uid'];
        $id = $info[5]['id'];

        $info = $this->send(RpcRequest::create("worklog.byUid", [$uid]));

        $this->assertEquals($id, $info['id']);

    }

}
