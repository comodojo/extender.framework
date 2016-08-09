<?php namespace Comodojo\Extender\Tests\Helpers;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class MockSignalListener extends AbstractListener {

    public function handle(EventInterface $event) {

        $process = $event->getProcess();

        $signals = array_merge($process->signals, array($event->getSignal()));

        $process->signals = $signals;

    }

}
