<?php namespace Comodojo\Extender\Tests\Task;

use \Comodojo\Extender\Tests\Helpers\MockTask;
use \Comodojo\Extender\Tests\Helpers\AbstractTestCase;
use \Comodojo\Extender\Task\Table;
use \Comodojo\Extender\Task\Runner;
use \Comodojo\Extender\Task\TaskParameters;
use \Comodojo\Extender\Orm\Entities\Worklog;

class RunnerTest extends AbstractTestCase {

    protected $table;

    protected $runner;

    protected function setUp() {

        $this->table = new Table(self::$configuration, self::$logger, self::$events);
        $this->table->add('test', '\Comodojo\Extender\Tests\Helpers\MockTask', 'mocktask');

        $this->runner = new Runner(self::$configuration, self::$logger, $this->table, self::$events, self::$em);

    }

    public function testExecution() {

        $result = $this->runner->run('runnertest','test');

        $this->assertInstanceOf('\Comodojo\Extender\Task\Result', $result);

        $this->assertEquals(42, $result->result);
        $this->assertEquals('runnertest', $result->name);
        $this->assertEquals(Worklog::STATUS_FINISHED, $result->success);

    }

    public function testAbortedTask() {

        $params = new TaskParameters();
        $params->set("abort", true);

        $result = $this->runner->run('runnertest','test', null, $params);

        $this->assertInstanceOf('\Comodojo\Extender\Task\Result', $result);

        $this->assertStringEndsWith("Vogon's poetry", $result->result);
        $this->assertEquals('runnertest', $result->name);
        $this->assertEquals(Worklog::STATUS_ABORTED, $result->success);

    }

    public function testCopyTask() {

        $copy = 'Answer to the Ultimate Question of Life, the Universe, and Everything';

        $params = new TaskParameters();
        $params->set("copy", $copy);

        $result = $this->runner->run('runnertest','test', null, $params);

        $this->assertInstanceOf('\Comodojo\Extender\Task\Result', $result);

        $this->assertEquals($copy, $result->result);
        $this->assertEquals('runnertest', $result->name);
        $this->assertEquals(Worklog::STATUS_FINISHED, $result->success);

    }

    public function testFailingTask() {

        $params = new TaskParameters();
        $params->set("fail", true);

        $result = $this->runner->run('runnertest','test', null, $params);

        $this->assertInstanceOf('\Comodojo\Extender\Task\Result', $result);

        $this->assertStringEndsWith("Vogon's poetry", $result->result);
        $this->assertEquals('runnertest', $result->name);
        $this->assertEquals(Worklog::STATUS_ERROR, $result->success);

    }

}
