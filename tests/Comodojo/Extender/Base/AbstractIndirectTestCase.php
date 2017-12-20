<?php namespace Comodojo\Extender\Tests\Base;

use \PHPUnit\Framework\TestCase;
use \Comodojo\Daemon\Socket\Client;
use \Exception;

abstract class AbstractIndirectTestCase extends TestCase {

    protected static $configuration;

    // protected $client;

    public static function setUpBeforeClass() {

        self::$configuration = ConfigurationLoader::getConfiguration();

    }

    // public function setUp() {
    //
    //     $this->client = Client::create(static::$configuration->get('sockethandler'));
    //
    // }
    //
    // public function tearDown() {
    //
    //     $this->client->close();
    //
    // }

    public function send($command, $payload = null) {

        try {

            $client = Client::create(static::$configuration->get('sockethandler'));

            $response = $client->send($command, $payload);

            $client->close();

        } catch (Exception $e) {

            throw $e;

        }

        return $response;

    }

}
