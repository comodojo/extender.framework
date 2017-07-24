<?php namespace Comodojo\Extender\Tests\Task;

use \Comodojo\Extender\Tests\Helpers\MockTask;
use \Comodojo\Extender\Tests\Helpers\AbstractTestCase;
use \Comodojo\Extender\Task\Table;
use \Comodojo\Extender\Task\Manager;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Extender\Task\TaskParameters;
use \Comodojo\Extender\Orm\Entities\Worklog;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Extender\Components\Database;
use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

$loader = require __DIR__ . "/../../../vendor/autoload.php";

// add PSR4 test classes
$loader->addPsr4('Comodojo\\Extender\\Tests\\', __DIR__."/../");

class ManagerMulti {

    protected static $configuration;

    protected static $logger;

    protected static $events;

    protected static $em;

    protected $table;

    public function __construct() {

        $base_path = realpath(dirname(__FILE__)."/../../../");
        $root_path = "$base_path/tests/root";

        $conf_file = "$root_path/config/comodojo-configuration.yml";
        $conf_data = Yaml::parse(file_get_contents($conf_file));

        $conf_data['base-path'] = $root_path;
        self::$configuration = new Configuration($conf_data);

        self::$em = Database::init(self::$configuration)->getEntityManager();

        // self::$configuration->set('base-path', $root_path);

        self::$logger = new Logger('test');
        //self::$logger->pushHandler( new NullHandler(Logger::DEBUG) );
        self::$events = new EventsManager(self::$logger);

        $this->table = new Table(self::$configuration, self::$logger, self::$events);
        $this->table->add('test', '\Comodojo\Extender\Tests\Helpers\MockTask', 'mocktask');

        self::$configuration->set('multithread', true);
        $manager = $this->createManager();

        for ($i=0; $i < 5; $i++) {
            $manager->add(
                new Request(
                    "runnertest_$i",
                    'test',
                    new TaskParameters([
                        'sleep' => rand(1,5)
                    ])
                )
            );
        }

        $results = $manager->run();

        var_dump($results);

    }

    protected function createManager() {

        return new Manager(
            "manager-test",
            self::$configuration,
            self::$logger,
            $this->table,
            self::$events,
            self::$em
        );

    }

}

$multi = new ManagerMulti();
