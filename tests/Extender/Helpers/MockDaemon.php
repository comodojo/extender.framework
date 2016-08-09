<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Extender\Base\Daemon;
use \Comodojo\Dispatcher\Components\Configuration;
use \League\Event\Emitter;
use \Psr\Log\LoggerInterface;

class MockDaemon extends Daemon {

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Emitter $events,
        $looptime = 1,
        $niceness = null)
    {

        parent::__construct($configuration, $logger, $events, $looptime, $niceness);

    }

    public function loop() {

        $this->logger->info("Now looping: ".$this->loopcount." iterations");

        if ($this->loopcount == 5) $this->stop();

    }

}
