<?php namespace Comodojo\Extender\Tests\Base;

use \Comodojo\Foundation\Base\Configuration;
use \Symfony\Component\Yaml\Yaml;

class ConfigurationLoader {

    public static function getConfiguration() {

        $base_path = realpath(dirname(__FILE__)."/../../../../");

        $root_path = "$base_path/tests/root";

        $conf_file = "$root_path/config/comodojo-configuration.yml";

        $conf_data = Yaml::parse(file_get_contents($conf_file));

        return new Configuration($conf_data);

    }

}
