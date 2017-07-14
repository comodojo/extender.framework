<?php namespace Comodojo\Extender;

use \Comodojo\Extender\Workers\ScheduleWorker;
use \Comodojo\Extender\Workers\QueueWorker;
use \Comodojo\Daemon\Daemon as AbstractDaemon;
use \Comodojo\Dispatcher\Traits\EventsTrait;
use \Comodojo\Dispatcher\Traits\LoggerTrait;
use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Dispatcher\Traits\ConfigurationTrait;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\SimpleCache\Manager as SimpleCacheManager;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Psr\Log\LoggerInterface;

class ExtenderDaemon extends AbstractDaemon {

    use ConfigurationTrait;
    use EventsTrait;
    use LoggerTrait;
    use CacheTrait;

    protected static $default_properties = array(
        'pidfile' => 'extender.pid',
        'socketfile' => 'unix://extender.sock',
        'socketbuffer' => 8192,
        'sockettimeout' => 15,
        'niceness' => 0,
        'arguments' => '\\Comodojo\\Daemon\\Console\\DaemonArguments',
        'description' => 'Extender Daemon'
    );

    public function __construct(
        array $configuration = [],
        EventsManager $events = null,
        SimpleCacheManager $cache = null,
        LoggerInterface $logger = null
    ) {

        $this->configuration = new Configuration(self::$default_properties);
        $this->configuration->merge($configuration);

        // $this->logger = is_null($logger) ? LogManager::createFromConfiguration($this->configuration)->getLogger() : $logger;

        try {
            // init other components
            //$this->setEvents(is_null($events) ? EventsManager::create($this->logger) : $events);

        } catch (Exception $e) {
            throw $e;
        }

        parent::__construct($this->configuration->get(), $logger, $events);

        $this->setCache(is_null($cache) ? SimpleCacheManager::createFromConfiguration($this->configuration, $this->logger) : $cache);

    }

    public function setup() {

        // add workers
        $manager = $this->getWorkers();
        $schedule_worker = new ScheduleWorker("schedule");
        $queue_worker = new QueueWorker("queue");
        $manager->install($schedule_worker, 1, true)
            ->install($queue_worker, 1, true);

    }

}
