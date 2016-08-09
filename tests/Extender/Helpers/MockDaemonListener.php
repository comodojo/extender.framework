<?php namespace Comodojo\Extender\Tests\Helpers;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class MockDaemonListener extends AbstractListener {

    public function handle(EventInterface $event) {

        $daemon = $event->getDaemon();

        if ($event->getEvent() == "loopstop" and $daemon->loopcount == 3) {
            $daemon->logger->info('loopcount = 3');
            $daemon->istest = true;
            $daemon->pause();
        }

        if ($event->getEvent() == "postloop" and !$daemon->isLooping() ) {
            $daemon->resume();
        }

    }

}
