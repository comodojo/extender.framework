<?php namespace Comodojo\Extender\Tests\Helpers;

use \Comodojo\Extender\Base\Process;

class MockProcess extends Process {

    protected $data = array(
        "signals" => array()
    );

    public function shutdown() {
        return;
    }

}
