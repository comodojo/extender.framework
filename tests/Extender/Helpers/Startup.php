<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\EventsManager;
use \Monolog\Logger;
use \Monolog\Handler\NullHandler;


class Startup extends \PHPUnit_Framework_TestCase {

    protected static $configuration_parameters = array(
        "is-test" => true,
        "log" => array(
            "name" => "test",
            "providers" => array(
                "test" => array(
                    "type" => "StreamHandler",
                    "stream" => "log/test.log",
                    "level" => "debug"
                )
            )
        ),

    );

    protected static $configuration;

    protected static $logger;

    protected static $events;

    public static function setUpBeforeClass() {

        $config = array_merge(self::$configuration_parameters, array(
            "base-path" => realpath(dirname(__FILE__)."/../../resources/"),
            "pid-file" => realpath(dirname(__FILE__)."/../../resources/cache")."/extender.pid",
            "run-file" => realpath(dirname(__FILE__)."/../../resources/cache")."/extender.run",
            "database" => array(
                'dbname' => 'sqlite',
                'driver' => 'pdo_sqlite',
                'path' => realpath(dirname(__FILE__)."/../../resources/database")."/extender.sqlite"
            ),
            'database-worklogs-table' => 'worklogs',
            'database-jobs-table' => 'jobs',
            'database-queue-table' => 'queue'
        ));

        self::$configuration = new Configuration($config);

        self::$logger = new Logger('test');
        self::$logger->pushHandler( new NullHandler(Logger::DEBUG) );
        self::$events = new EventsManager(self::$logger);

    }

}
