<?php namespace Comodojo\Extender\Tests\Tasks;

use \Comodojo\Extender\Tests\Helpers\Startup;
use \Comodojo\Extender\Tests\Helpers\MockTask;
use \Comodojo\Extender\Tasks\Table;
use \Comodojo\Extender\Tasks\Runner;

class RunnerTest extends Startup {

    protected $table;

    protected $runner;

    protected function setUp() {

        $this->table = new Table(self::$logger);
        $this->table->add('test','\Comodojo\Extender\Tests\Helpers\MockTask','mocktask');

        $this->runner = new Runner(self::$configuration, self::$logger, $this->table, self::$events);

    }

    public function testExecution() {

        $result = $this->runner->run('runnertest','test');

        $this->assertInstanceOf('\Comodojo\Extender\Tasks\Result', $result);

        $this->assertEquals(42, $result->result);
        $this->assertEquals('runnertest', $result->name);
        $this->assertTrue($result->success);

    }

    public function testFailingTask() {

        $result = $this->runner->run('runnertest','test', null, array("fail"=>true));

        $this->assertInstanceOf('\Comodojo\Extender\Tasks\Result', $result);

        $this->assertStringEndsWith("Vogon's poetry", $result->result);
        $this->assertEquals('runnertest', $result->name);
        $this->assertFalse($result->success);
        
    }

}
