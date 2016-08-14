<?php namespace Comodojo\Extender\Tests\Components;

use \Comodojo\Extender\Tests\Helpers\Startup;
use \Comodojo\Extender\Components\RunLock;

class RunLockTest extends Startup {

    public function testRunLock() {

        $lockfile = self::$configuration->get("run-file");

        $runlock = new RunLock($lockfile);

        $result = $runlock->lock();

        $this->assertNotFalse($result);

        $this->assertFileExists($lockfile);

        $this->assertStringEqualsFile($lockfile, "RUNNING");

        $result = $runlock->pause();

        $this->assertNotFalse($result);

        $this->assertStringEqualsFile($lockfile, "PAUSED");

        $result = $runlock->check();

        $this->assertFalse($result);

        $result = $runlock->resume();

        $this->assertNotFalse($result);

        $result = $runlock->check();

        $this->assertTrue($result);

        $this->assertStringEqualsFile($lockfile, "RUNNING");

        $result = $runlock->release();

        $this->assertTrue($result);

        $this->assertFileNotExists($lockfile);

    }

}
