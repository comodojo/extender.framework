<?php

require 'vendor/autoload.php';

try {

    $client = \Comodojo\Daemon\Socket\Client::create('unix://extender.sock');

    $data = $client->send('scheduler:refresh',[]);

} catch (Exception $e) {

    exit("\n".$e->getMessage()."\n\n");

}

var_dump($data);
