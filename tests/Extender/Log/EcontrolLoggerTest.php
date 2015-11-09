<?php

use \Comodojo\Extender\Log\EcontrolLogger;

class EcontrolLoggerTest extends \PHPUnit_Framework_TestCase {

    public function testLogger() {

        $logger = EcontrolLogger::create(true);

        $this->assertInstanceOf('\Monolog\Logger', $logger);

    }
    
}
