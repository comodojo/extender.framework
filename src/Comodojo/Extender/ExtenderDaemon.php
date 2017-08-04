<?php namespace Comodojo\Extender;

use \Comodojo\Extender\Workers\ScheduleWorker;
use \Comodojo\Extender\Workers\QueueWorker;
use \Comodojo\Extender\Task\Table as TasksTable;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Extender\Task\TaskParameters;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Queue\Manager as QueueManager;
use \Comodojo\Daemon\Daemon as AbstractDaemon;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\Foundation\Utils\ArrayOps;
use \Comodojo\SimpleCache\Manager as SimpleCacheManager;
use \Doctrine\ORM\EntityManager;
use \Psr\Log\LoggerInterface;

class ExtenderDaemon extends AbstractDaemon {

    use ConfigurationTrait;
    use EventsTrait;
    use LoggerTrait;
    use CacheTrait;
    use TasksTableTrait;
    use EntityManagerTrait;

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
        array $configuration,
        array $tasks,
        EventsManager $events = null,
        SimpleCacheManager $cache = null,
        LoggerInterface $logger = null
    ) {

        $this->configuration = new Configuration(self::$default_properties);
        $this->configuration->merge($configuration);

        parent::__construct(ArrayOps::replaceStrict(self::$default_properties, $this->configuration->get()), $logger, $events);

        $table = new TasksTable($this->configuration, $this->getLogger(), $this->getEvents());
        $table->addBulk($tasks);
        $this->setTasksTable($table);

        $this->setCache(is_null($cache) ? SimpleCacheManager::createFromConfiguration($this->configuration, $this->logger) : $cache);

        $this->setEntityManager(Database::init($this->configuration)->getEntityManager());

    }

    public function setup() {

        $this->installWorkers();

        $this->pushQueueCommands();
        $this->pushScheduleCommands();

    }

    protected function installWorkers() {

        // add workers
        $manager = $this->getWorkers();

        $schedule_worker = new ScheduleWorker("scheduler");
        $schedule_worker
            ->setConfiguration($this->getConfiguration())
            ->setLogger($this->getLogger())
            ->setEvents($this->getEvents())
            ->setTasksTable($this->getTasksTable())
            ->setEntityManager($this->getEntityManager());

        $queue_worker = new QueueWorker("queue");
        $queue_worker
            ->setConfiguration($this->getConfiguration())
            ->setLogger($this->getLogger())
            ->setEvents($this->getEvents())
            ->setTasksTable($this->getTasksTable())
            ->setEntityManager($this->getEntityManager());

        $manager
            ->install($schedule_worker, 1, true)
            ->install($queue_worker, 1, true);

    }

    protected function pushQueueCommands() {

        $this->getSocket()->getCommands()
            ->add('queue:add', function(Request $request, $daemon) {
                $manager = new QueueManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents(),
                    $this->getEntityManager()
                );

                return $manager->add($name, $request);
            })
            ->add('queue:addBulk', function(array $requests, $daemon) {

                $manager = new QueueManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents(),
                    $this->getEntityManager()
                );

                return $manager->addBulk($requests);

            });

    }

    protected function pushScheduleCommands() {

        $this->getSocket()->getCommands()->add('scheduler:refresh', function($data, $daemon) {

            return $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

        });

    }

}
