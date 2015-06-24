<?php

// framework variables

define("EXTENDER_CACHE_FOLDER", realpath(dirname(__FILE__))."/resources/cache/");

// Simple bootloader for phpunit using composer autoloader

$loader = require __DIR__ . "/../vendor/autoload.php";
