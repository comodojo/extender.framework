<?php namespace Comodojo\Extender\Tests\Jobs;

use \Comodojo\Extender\Tests\Helpers\Startup;
use \Comodojo\Extender\Tests\Helpers\MockTask;
use \Comodojo\Extender\Tasks\Table;
use \Comodojo\Extender\Jobs\Runner;
use \Comodojo\Extender\Jobs\Job;

class RunnerTest extends Startup {

    protected $jobone;

    protected $jobtwo;

    protected function setUp() {

        $this->jobone = new Job(
            "one",
            1,
            'test',
            null,
            null,
            array("copy" => "slartibartfast")
        );

        $this->jobtwo = new Job(
            "two",
            2,
            'test',
            null,
            null,
            array("copy" => "beeblebrox")
        );

    }

    public function testSinglethread() {

        $table = new Table(self::$logger);
        $table->add('test','\Comodojo\Extender\Tests\Helpers\MockTask','MockTask');

        $runner = new Runner(self::$configuration, self::$logger, $table, self::$events);

        $result = $runner->add($this->jobone);
        $this->assertTrue($result);

        $result = $runner->add($this->jobtwo);
        $this->assertTrue($result);

        $result = $runner->run();

        foreach ($result as $job) {
            $this->assertTrue($job->success);
            $copy = $job->parameters['copy'];
            $this->assertEquals($copy, $job->result);
        }

    }

    public function testMultithread() {

        $table = new Table(self::$logger);
        $table->add('test','\Comodojo\Extender\Tests\Helpers\MockTask','MockTask');

        $configuration = self::$configuration->merge(array(
            'multithread' => true
        ));

        $runner = new Runner(self::$configuration, self::$logger, $table, self::$events);

        $result = $runner->add($this->jobone);
        $this->assertTrue($result);

        $result = $runner->add($this->jobtwo);
        $this->assertTrue($result);

        $result = $runner->run();

        foreach ($result as $job) {
            $this->assertTrue($job->success);
            $copy = $job->parameters['copy'];
            $this->assertEquals($copy, $job->result);
        }

    }

    public function testForkLimit() {

        $jobs = array();

        for ($i=1; $i <= 50; $i++) {
            $jobs[] = new Job(
                $i,
                $i,
                'test',
                null,
                null,
                array("copy" => "beeblebrox", "sleep" => rand(1,5))
            );
        }

        $table = new Table(self::$logger);
        $table->add('test','\Comodojo\Extender\Tests\Helpers\MockTask','MockTask');

        $configuration = self::$configuration->merge(array(
            'multithread' => true,
            'fork-limit' => 8
        ));

        $runner = new Runner(self::$configuration, self::$logger, $table, self::$events);

        foreach ($jobs as $job) {
            $result = $runner->add($job);
            $this->assertTrue($result);
        }

        $result = $runner->add($this->jobtwo);
        $this->assertTrue($result);

        $result = $runner->run();

        foreach ($result as $job) {
            $this->assertTrue($job->success);
            $copy = $job->parameters['copy'];
            $this->assertEquals($copy, $job->result);
        }

    }

}
