<?php namespace Comodojo\Extender\Listeners;

use \League\Event\AbstractListener;
use \League\Event\EventInterface;
use \DateTime;

class StopSummary extends AbstractListener {

    public function handle(EventInterface $event) {

        $daemon = $event->getDaemon();
        $console = $daemon->console;

        $chars = 60;

        // show the header
        $console->br()->border('-',$chars);
        $console->bold()->flank('Extender execution summary');
        $console->border('-',$chars);
        $console->out('Total run time: '.self::calculateRunTime($daemon->starttime));
        $console->border('-',$chars);
        $padding = $console->padding(26);
        $padding->label('Total processed jobs')->result($daemon->completedjobs+$daemon->failedjobs);
        $padding = $console->padding(30);
        $padding->label('├─ Completed')->result('<light_green>'.$daemon->completedjobs.'</green>');
        $padding->label('└─ Failed')->result('<red>'.$daemon->failedjobs.'</red>');
        $console->border('-',$chars);

    }

    private static function calculateRunTime($starttime) {

        $start_formatted = sprintf("%06d",($starttime - floor($starttime)) * 1000000);
        $start = new DateTime( date('Y-m-d H:i:s.'.$start_formatted, $starttime) );
        $end = new DateTime();

        $diff = $end->diff($start);

        return sprintf("%d days %d hours %d minutes %d seconds",
            $diff->d,
            $diff->h,
            $diff->i,
            $diff->s
        );

    }

}
