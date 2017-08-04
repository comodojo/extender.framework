<?php

require 'vendor/autoload.php';

try {

    $client = \Comodojo\Daemon\Socket\Client::create('unix://extender.sock');

    // $data = $client->send('queue:add', ['name'=>'test', 'task'=>'test', 'parameters'=>['copy'=>'fooboo']]);

    $data = $client->send('queue:addBulk', [
        \Comodojo\Extender\Task\Request::create(
            'testfail',
            'test',
            new \Comodojo\Extender\Task\TaskParameters(['sleep'=>10])
        )->setMaxtime(5)->setNiceness(2)->onFail(
            \Comodojo\Extender\Task\Request::create(
                'testisfailed',
                'test'
            )
        ),
        \Comodojo\Extender\Task\Request::create(
            'testchain',
            'test'
        )->pipe(
            \Comodojo\Extender\Task\Request::create(
                'testchainpipe',
                'test'
            )
        )->onDone(
            \Comodojo\Extender\Task\Request::create(
                'testchaindone',
                'test'
            )->onDone(
                \Comodojo\Extender\Task\Request::create(
                    'testchainleveltwodone',
                    'test'
                )
            )
        )->onFail(
            \Comodojo\Extender\Task\Request::create(
                'testchainfail',
                'test'
            )
        )
    ]);

} catch (Exception $e) {

    exit("\n".$e->getMessage()."\n\n");

}

var_dump($data);
