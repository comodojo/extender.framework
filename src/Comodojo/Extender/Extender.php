<?php namespace Comodojo\Extender;

use \Comodojo\Extender\Base\Daemon;
use \Comodojo\Extender\Components\DefaultConfiguration;
use \Comodojo\Extender\Components\Version;
use \Comodojo\Extender\Console\Arguments as ConsoleArgumentsTrait;
use \Comodojo\Extender\Console\LogHandler;
use \Comodojo\Extender\Jobs\Results;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\LogManager;
use \Comodojo\Dispatcher\Components\EventsManager;
use \Comodojo\Dispatcher\Components\CacheManager;
use \Comodojo\Cache\Cache;
use \League\CLImate\CLImate;
use \Psr\Log\LoggerInterface;

class Extender extends Daemon {

    use ConsoleArgumentsTrait;

    public function __construct(
        $configuration = array(),
        LoggerInterface $logger = null,
        EventsManager $events = null,
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
        $this->currentjobs = new Results();

        // init the console
        $this->console = new CLImate();
        $this->console->description(Version::getFullDescription($this->configuration));
        $this->console->arguments->add($this->console_arguments);
        $this->console->arguments->parse();

        // init the logger
        $this->logger = is_null($logger) ? LogManager::create($this->configuration, 'extender') : $logger;
        if ( $this->console->arguments->get('verbose') === true ) {
            $this->logger->pushHandler(new LogHandler());
        }

        // init event manager
        $this->events = is_null($events) ? new EventsManager($this->logger) : $events;

        // init the cache abstraction layer
        $this->cache = is_null($cache) ? CacheManager::create($this->configuration, $this->logger) : $cache;

        // install the loop limiter
        $this->looplimit = $this->console->arguments->get('iterations');
        $this->events->subscribe("extender.daemon.loopstop", "\Comodojo\Extender\Listeners\LoopLimit");

        // install the summary report listener if requested
        if ( $this->console->arguments->get('summary') === true ) {
            $this->events->subscribe("extender.daemon.loopstop", "\Comodojo\Extender\Listeners\LoopSummary");
            $this->events->subscribe("extender.daemon.stop", "\Comodojo\Extender\Listeners\StopSummary");
        }

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

    public function extend() {

        if ( $this->console->arguments->get('help') === true ) {
            // show help and exit
            $this->console->usage();
            $this->end(0);
        } else if ( $this->console->arguments->get('daemon') === true ) {
            // run extender as a deamon
            $this->daemonize();
        } else {
            // run extender as a normal foreground process
            $this->start();
        }

    }

}
