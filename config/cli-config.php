<?php

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;
use \Doctrine\ORM\Tools\Console\ConsoleRunner;

$base_path = realpath(dirname(__FILE__)."/../");
$root_path = "$base_path/tests/root";

$loader = require "$base_path/vendor/autoload.php";

$conf_file = "$root_path/config/comodojo-configuration.yml";
$conf_data = Yaml::parse(file_get_contents($conf_file));
$conf_data['base-path'] = $root_path;

$configuration = new Configuration($conf_data);

$em = Database::init($configuration)->getEntityManager();

return ConsoleRunner::createHelperSet($em);
