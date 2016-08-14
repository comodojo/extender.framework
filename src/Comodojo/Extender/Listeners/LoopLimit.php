<?php namespace Comodojo\Extender\Listeners;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class LoopLimit extends AbstractListener {

    public function handle(EventInterface $event) {

        $daemon = $event->getDaemon();

        if ( $daemon->looplimit === $daemon->loopcount) {
            $daemon->logger->info('Stopping daemon due to loop limit ('.$daemon->looplimit.') reached');
            // $daemon->stop();
            $daemon->loopactive = false;
        }

    }

}
