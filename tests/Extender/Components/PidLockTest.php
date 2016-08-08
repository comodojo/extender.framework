<?php namespace Comodojo\Extender\Tests\Components;

use \Comodojo\Extender\Tests\Helpers\Startup;
use \Comodojo\Extender\Components\PidLock;

class PidLockTest extends Startup {

    public function testPidLock() {

        $pid = rand(100, 1000);

        $lockfile = self::$configuration->get("pid-file");

        $pidlock = new PidLock($pid, $lockfile);

        $result = $pidlock->lock();

        $this->assertNotFalse($result);

        $this->assertFileExists($lockfile);

        $this->assertStringEqualsFile($lockfile, $pid);

        $result = $pidlock->release();

        $this->assertTrue($result);

        $this->assertFileNotExists($lockfile);

    }

}
