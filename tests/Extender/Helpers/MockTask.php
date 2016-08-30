<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Extender\Tasks\AbstractTask;
use \Comodojo\Exception\TaskException;

class MockTask extends AbstractTask {

    public function run() {

        $copy = $this->parameters->get('copy');

        $sleep = $this->parameters->get('sleep');

        $fail = $this->parameters->get('fail');

        if ( $fail === true ) throw new TaskException("Task is failing due to Vogon's poetry");

        if ( $sleep !== null ) {
            $this->logger->debug("zZz... sleeping for $sleep seconds... zZz");
            sleep($sleep);
        }

        return is_null($copy) ? 42 : $copy;

    }

}
