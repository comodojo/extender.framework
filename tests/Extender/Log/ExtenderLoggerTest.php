<?php

use \Comodojo\Extender\Log\ExtenderLogger;

class ExtenderLoggerTest extends \PHPUnit_Framework_TestCase {

    public function testLogger() {

        $logger = ExtenderLogger::create(true);

        $this->assertInstanceOf('\Monolog\Logger', $logger);

    }
    
}
