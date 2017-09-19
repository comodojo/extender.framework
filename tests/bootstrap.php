<?php

// init the autoloader
$loader = require __DIR__ . "/../vendor/autoload.php";

// add PSR4 test classes
$loader->addPsr4('Comodojo\\Extender\\Tests\\', __DIR__."/Comodojo/Extender");
