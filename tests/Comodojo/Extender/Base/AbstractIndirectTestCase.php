<?php namespace Comodojo\Extender\Tests\Base;

use \PHPUnit\Framework\TestCase;
use \Comodojo\Daemon\Socket\SocketTransport;
use \Comodojo\RpcClient\RpcClient;
use \Comodojo\RpcClient\RpcRequest;
use \Exception;

abstract class AbstractIndirectTestCase extends TestCase {

    protected static $configuration;

    // protected $client;

    public static function setUpBeforeClass() {

        self::$configuration = ConfigurationLoader::getConfiguration();

    }

    public function setUp() {

        $socket_addr = self::$configuration->get('sockethandler');

        $transport = SocketTransport::create($socket_addr);

        $this->client = new RpcClient($socket_addr, null, $transport);

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

    public function send(RpcRequest $request) {

        try {

            $this->client->addRequest($request);

            return $this->client->send();

        } catch (Exception $e) {

            throw $e;

        }

    }

    public function bulkSend(...$requests) {

        try {

            foreach ($requests as $request) {
                $this->client->addRequest($request);
            }

            return $this->client->send();

        } catch (Exception $e) {

            throw $e;

        }

    }

}
