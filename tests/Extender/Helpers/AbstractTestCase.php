<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Extender\Components\Database;
use \Monolog\Logger;
use \Monolog\Handler\NullHandler;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

class AbstractTestCase extends \PHPUnit_Framework_TestCase {

    protected static $configuration;

    protected static $logger;

    protected static $events;

    protected static $em;

    public static function setUpBeforeClass() {

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

    }

}
