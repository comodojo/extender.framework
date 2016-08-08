<?php namespace Comodojo\Extender\Tests\Base;

use \Comodojo\Extender\Tests\Helpers\MockProcess;
use \Comodojo\Extender\Tests\Helpers\MockSignalListener;
use \Comodojo\Extender\Tests\Helpers\Startup;

class ProcessTest extends Startup {

    protected static $process;

    protected static $signals;

    public static function setUpBeforeClass() {

        parent::setUpBeforeClass();

        self::$process = new MockProcess(self::$configuration, self::$logger, self::$events);

    }

    public function testSignaling() {

        $signals = array(SIGUSR1, SIGUSR2);

        foreach ($signals as $signal) {
            self::$process->events->subscribe('extender.signal.'.$signal, '\Comodojo\Extender\Tests\Helpers\MockSignalListener');
        }

        $pid = self::$process->pid;

        foreach ($signals as $signal) {
            $send = posix_kill($pid, $signal);
            $this->assertTrue($send);
        }

        pcntl_signal_dispatch();

        foreach ($signals as $signal) {
            $this->assertTrue(in_array($signal, self::$process->signals));
        }

    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Test Exception
     */
    public function testEndInError() {

        self::$process->events->subscribe('extender.signal.'.SIGTERM, '\Comodojo\Extender\Tests\Helpers\MockSignalListener');

        $pid = self::$process->pid;

        $send = posix_kill($pid, SIGTERM);
        $this->assertTrue($send);

        pcntl_signal_dispatch();

    }

    public function testEndInErrorSignaling() {

        $this->assertTrue(in_array(SIGTERM, self::$process->signals));

    }

}
