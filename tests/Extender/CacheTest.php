<?php

class CacheTest extends \PHPUnit_Framework_TestCase {

    protected $cache_data = array(
        "sample"    =>  "cache",
        "data"      =>  "for",
        "unit"      =>  "testing"

    );

    public function testSet() {

        $result = \Comodojo\Extender\Cache::set($this->cache_data);

        $this->assertNotFalse($result);

        $this->assertGreaterThan(1, $result);

    }

    public function testGet() {

        $result = \Comodojo\Extender\Cache::get();

        $this->assertSame($this->cache_data, $result);

    }

    public function testPurge() {

        $result = \Comodojo\Extender\Cache::purge();

        $this->assertTrue($result);

    }
    
}
