<?php

require 'vendor/autoload.php';

try {

    $name = 'test_sched';

    $client = \Comodojo\Daemon\Socket\Client::create('unix://tests/root/run/extender.sock');

    $schedule = $client->send('scheduler:getByName', $name);

    if ( $schedule === null ) {

        $request = \Comodojo\Extender\Task\Request::create(
            'testchain',
            'test'
        )->pipe(
            \Comodojo\Extender\Task\Request::create(
                'testchainpipe',
                'test'
            )
        );

        $schedule = new \Comodojo\Extender\Orm\Entities\Schedule();

        $expression = Cron\CronExpression::factory('* * * * *');

        $schedule
            ->setName('test_sched')
            ->setDescription('This is a test')
            ->setRequest($request)
            ->setExpression($expression);

        $data = $client->send('scheduler:add', $schedule);

        $schedule = $client->send('scheduler:getByName', $name);

    }

    $data = $client->send('scheduler:enable', $name);

    // $data = $client->send('scheduler:edit', $schedule);

} catch (Exception $e) {

    exit("\n".$e->getMessage()."\n\n");

}

var_dump($data);
