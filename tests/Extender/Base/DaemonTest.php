<?php namespace Comodojo\Extender\Tests\Base;

use \Comodojo\Extender\Tests\Helpers\MockDaemon;
use \Comodojo\Extender\Tests\Helpers\MockSignalListener;
use \Comodojo\Extender\Tests\Helpers\Startup;

class DaemonTest extends Startup {

    protected static $daemon;

    public static function setUpBeforeClass() {

        parent::setUpBeforeClass();

        self::$daemon = new MockDaemon(self::$configuration, self::$logger, self::$events);

        self::$daemon->events->subscribe("extender.daemon.loopstop", "\Comodojo\Extender\Tests\Helpers\MockDaemonListener");
        self::$daemon->events->subscribe("extender.daemon.postloop", "\Comodojo\Extender\Tests\Helpers\MockDaemonListener");

    }

    public function testStartDaemon() {

        $this->assertNull(self::$daemon->istest);

        $exitcode = self::$daemon->start();

        $this->assertEquals(0, $exitcode);

        $this->assertTrue(self::$daemon->istest);

    }

}
