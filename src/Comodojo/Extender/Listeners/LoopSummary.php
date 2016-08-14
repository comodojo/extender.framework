<?php namespace Comodojo\Extender\Listeners;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;

class LoopSummary extends AbstractListener {

    public function handle(EventInterface $event) {

        $daemon = $event->getDaemon();
        $console = $daemon->console;

        $jobs = $daemon->currentjobs->export();

        if ( empty($jobs) ) return;

        $console->border('-',30);
        $console->bold()->green('Extender loop summary');
        $console->out("Loop duration: ".round($daemon->loopelapsed));
        $console->border('-',30);
        $console->out("Executed jobs: ");
        $console->table($jobs);

    }

}
