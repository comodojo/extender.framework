<?php namespace Comodojo\Extender\Tests\Base;

use \Comodojo\Extender\Tests\Helpers\MockDaemon;
use \Comodojo\Extender\Tests\Helpers\MockSignalListener;
use \Comodojo\Extender\Tests\Helpers\Startup;

class DaemonTest extends Startup {

    protected static $daemon;

    public static function setUpBeforeClass() {

        parent::setUpBeforeClass();

        self::$daemon = new MockDaemon(self::$configuration, self::$logger, self::$events);

        self::$daemon->daemon = true;

    }

    public function testStartDaemon() {

        $exitcode = self::$daemon->start();

        $this->assertEquals(0, $exitcode);

    }

    //
    // public function running($pid) {
    //
    //     //$this->assertTrue(self::$daemon->running);
    //     //$pid = self::$daemonpid;
    //     //self::$daemon->pause();
    //     $send = posix_kill($pid, SIGTSTP);
    //     // $this->assertFalse(self::$daemon->running);
    //
    //     //self::$daemon->resume();
    //     $send = posix_kill($pid, SIGCONT);
    //     // $this->assertTrue(self::$daemon->running);
    //
    // }
    //
    // public function killDaemon($pid) {
    //
    //     sleep(4);
    //
    //     //$pid = self::$daemonpid;
    //
    //     $send = posix_kill($pid, SIGTERM);
    //     $this->assertTrue($send);
    //
    // }

}
