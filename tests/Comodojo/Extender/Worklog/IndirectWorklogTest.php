<?php namespace Comodojo\Extender\Tests\Worklog;

use Comodojo\Extender\Tests\Base\AbstractIndirectTestCase;

class IndirectWorklogTest extends AbstractIndirectTestCase {

    public function testWorklogCount() {

        $info = $this->send('worklog:count');

        $this->assertGreaterThan(1, $info);

    }

    public function testWorklogListMethods() {

        $info = $this->send('worklog:list');

        $this->assertInternalType('array', $info);
        $this->assertCount(10, $info);

        $info = $this->send('worklog:list', ['limit' => 11]);

        $this->assertInternalType('array', $info);
        $this->assertCount(11, $info);

        $uid = $info[5]['uid'];
        $id = $info[5]['id'];

        $info = $this->send('worklog:getByUid', $uid);
        $this->assertEquals($id, $info['id']);

    }

}
