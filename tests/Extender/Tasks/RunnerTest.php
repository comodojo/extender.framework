<?php namespace Comodojo\Extender\Tests\Tasks;

use \Comodojo\Extender\Tests\Helpers\Startup;
use \Comodojo\Extender\Tests\Helpers\MockTask;
use \Comodojo\Extender\Tasks\Table;
use \Comodojo\Extender\Tasks\Runner;

class RunnerTest extends Startup {

    public function testExecution() {

        $table = new Table(self::$logger);
        $table->add('test','\Comodojo\Extender\Tests\Helpers\MockTask','mocktask');

        $runner = new Runner(self::$configuration, self::$logger, $table);

        $result = $runner->run('runnertest','test');

        $this->assertInstanceOf('\Comodojo\Extender\Tasks\Result', $result);

        var_export($result);

    }

}
