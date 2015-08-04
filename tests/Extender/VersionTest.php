<?php

class VersionTest extends \PHPUnit_Framework_TestCase {


    public function testGetDescription() {

        $result = \Comodojo\Extender\Version::getDescription();

        $this->assertInternalType('string', $result);

    }

    public function testGetVersion() {

        $result = \Comodojo\Extender\Version::getVersion();

        $this->assertInternalType('string', $result);

    }
    
}
