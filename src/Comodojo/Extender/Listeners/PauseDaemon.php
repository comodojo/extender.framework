<?php namespace Comodojo\Extender\Listeners;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class PauseDaemon extends AbstractListener {

    public function handle(EventInterface $event) {
        
        $daemon = $event->getDaemon();

        $daemon->runlock->pause();

    }

}
