<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Extender\Task\AbstractTask;
use \Comodojo\Exception\TaskException;
use \Exception;

class MockTask extends AbstractTask {

    public function run() {

        $parametes = $this->getParameters();

        $copy = $parametes->get('copy');

        $sleep = $parametes->get('sleep');

        $abort = $parametes->get('abort');

        $fail = $parametes->get('fail');

        if ( $abort === true ) throw new TaskException("Task aborted due to Vogon's poetry");

        if ( $fail === true ) throw new Exception("Task is failing due to Vogon's poetry");

        if ( $sleep !== null ) {
            $this->logger->debug("zZz... sleeping for $sleep seconds... zZz");
            sleep($sleep);
        }

        return is_null($copy) ? 42 : $copy;

    }

}
