<?php namespace Comodojo\Extender\Tests\Helpers;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class MockSignalListener extends AbstractListener {

    public function handle(EventInterface $event) {

        $signals = array_merge($event->process->signals, array($event->getSignal()));

        $event->process->signals = $signals;

    }

}
