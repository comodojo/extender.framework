<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Extender\Tasks\AbstractTask;

class MockTask extends AbstractTask {

    public function run() {

        $copy = $this->parameters->get('copy');

        $sleep = $this->parameters->get('sleep');

        if ( $sleep !== null ) {
            $this->logger->debug("zZz... sleeping for $sleep seconds... zZz");
            sleep($sleep);
        }

        return is_null($copy) ? 42 : $copy;

    }

}
