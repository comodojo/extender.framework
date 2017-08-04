<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Extender\Task\AbstractTask;
use \Comodojo\Exception\TaskException;
use \Exception;

class MockTask extends AbstractTask {

    public function run() {

        $parameters = $this->getParameters();

        $copy = $parameters->get('copy');

        $sleep = $parameters->get('sleep');

        $abort = $parameters->get('abort');

        $fail = $parameters->get('fail');

        $parent = $parameters->get('parent');

        if ($parent !== null) $this->getLogger()->info("I'm a child, my parent returned ".$parent->result);

        if ( $abort === true ) throw new TaskException("Task aborted due to Vogon's poetry");

        if ( $fail === true ) throw new Exception("Task is failing due to Vogon's poetry");

        if ( $sleep !== null ) {
            $this->logger->debug("zZz... sleeping for $sleep seconds... zZz");
            sleep($sleep);
        }

        return is_null($copy) ? 42 : $copy;

    }

}
