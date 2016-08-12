<?php namespace Comodojo\Extender;

use \Comodojo\Extender\Base\Daemon;
use \Comodojo\Extender\Components\DefaultConfiguration;
use \Comodojo\Extender\Components\LogManager;
use \Comodojo\Extender\Components\Version;
use \Comodojo\Extender\Console\Arguments as ConsoleArgumentsTrait;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\EventsManager;
use \Comodojo\Dispatcher\Components\CacheManager;
use \Comodojo\Cache\Cache;
use \League\Event\Emitter;
use \League\CLImate\CLImate;
use \Psr\Log\LoggerInterface;

class Extender extends Daemon {

    use ConsoleArgumentsTrait;

    public function __construct(
        $configuration = array(),
        LoggerInterface $logger = null,
        Emitter $events = null,
        Cache $cache = null
    ) {

        // parsing configuration
        $this->configuration = new Configuration( DefaultConfiguration::get() );
        $this->configuration->merge($configuration);

        // fix the daemon start time
        $this->starttime = microtime(true);

        // init counters
        $this->completedjobs = 0;
        $this->failedjobs = 0;

        // init the console
        $this->console = new CLImate();
        $this->console->description(Version::getFullDescription($this->configuration));
        $this->console->arguments->add($this->console_arguments);
        $this->console->arguments->parse();

        // check for help request
        $this->helpMe($this->console->arguments->get('help'));

        // init core components
        $this->logger = new \Monolog\Logger('test'); //is_null($logger) ? LogManager::create($this->configuration) : $logger;
        $this->events = is_null($events) ? new EventsManager($this->logger) : $events;
        $this->cache = is_null($cache) ? CacheManager::create($this->configuration, $this->logger) : $cache;

        // install the loop limiter
        $this->events->subscribe("extender.daemon.loopstop", "\Comodojo\Extender\Listeners\LoopLimit");

        // get daemon parameters
        $looptime = $this->configuration->get('looptime');
        $niceness = $this->configuration->get('niceness');

        // build daemon
        parent::__construct($this->configuration, $this->logger, $this->events, $looptime, $niceness);

    }

    public function loop() {

        // fix the relative timestamp
        $this->setTimestamp();

        // check plans (if any) and return if no plans for this loop

        // if no plans, retrieve schedule and build plan cache

        // load jobs in runner (if any)

        // go runner, go!

        // collect job result

        // update counters

    }

    private function helpMe($help) {

        if ( $help === true ) {

            // show help and exit
            $this->console->usage();
            $this->end(0);

        }

    }

    private function loopUntil($loopcount) {

        $this->configuration->set('loop-limit', $loopcount);

    }

    private function statusMe($status) {

    }

}
