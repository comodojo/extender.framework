<?php namespace Comodojo\Extender\Tests\Jobs;

use \Comodojo\Extender\Tests\Helpers\Startup;
use \Comodojo\Extender\Tests\Helpers\MockTask;
use \Comodojo\Extender\Tasks\Table;
use \Comodojo\Extender\Utils\ProcessTools;
use \Comodojo\Extender\Jobs\Runner;
use \Comodojo\Extender\Jobs\Job;

class RunnerTest extends Startup {

    protected $jobone;

    protected $jobtwo;

    protected $jobs;

    protected $table;

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

        $this->jobs = array();

        for ($i=1; $i <= 50; $i++) {
            $this->jobs[] = new Job(
                $i,
                $i,
                'test',
                null,
                null,
                array("copy" => "beeblebrox", "sleep" => rand(1,5))
            );
        }

        $this->table = new Table(self::$logger);
        $this->table->add('test','\Comodojo\Extender\Tests\Helpers\MockTask','MockTask');

    }

    public function testSinglethread() {

        $runner = new Runner(self::$configuration, self::$logger, $this->table, self::$events);

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

        $configuration = self::$configuration->merge(array(
            'multithread' => true
        ));

        $runner = new Runner(self::$configuration, self::$logger, $this->table, self::$events);

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

        $configuration = self::$configuration->merge(array(
            'multithread' => true,
            'fork-limit' => 8
        ));

        $runner = new Runner(self::$configuration, self::$logger, $this->table, self::$events);

        foreach ($this->jobs as $job) {
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
