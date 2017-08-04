<?php namespace Comodojo\Extender\Listeners;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class RefreshScheduler extends AbstractListener {

    public function handle(EventInterface $event) {

        $worker = $event->getWorker()->getInstance();

        $logger = $worker->getLogger();

        $logger->info("Plans changed, refreshing schedule");

        $worker->refreshPlans();

        return true;

    }

}
