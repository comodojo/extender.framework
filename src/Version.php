<?php namespace Comodojo\Extender;

class Version {
	
	static private $description = "Database driven, multiprocess, pseudo-cron tasks executor";

	static private $version = "1.0.0";

	static final public function getDescription() {

		return self::ascii()."\n".self::$description."\n";

	}

	static final public function getVersion() {

		return self::$version;

	}

	static private function ascii() {

		$ascii = "\n   ______                                 __              __        \r\n";
		$ascii .= "  / ____/  ____    ____ ___   ____   ____/ /  ____       / /  ____ \r\n";
		$ascii .= " / /      / __ \  / __ `__ \ / __ \ / __  /  / __ \     / /  / __ \ \r\n";
		$ascii .= "/ /___   / /_/ / / / / / / // /_/ // /_/ /  / /_/ /    / /  / /_/ /\r\n";
		$ascii .= "\____/   \____/ /_/ /_/ /_/ \____/ \__,_/   \____/  __/ /   \____/ \r\n";
		$ascii .= "-------------------------------------------------  /___/  ---------\r\n";
		$ascii .= "                 __                      __                        \r\n";
		$ascii .= "  ___    _  __  / /_  ___    ____   ____/ /  ___    _____          \r\n";
		$ascii .= " / _ \  | |/_/ / __/ / _ \  / __ \ / __  /  / _ \  / ___/          \r\n";
		$ascii .= "/  __/ _>  <  / /_  /  __/ / / / // /_/ /  /  __/ / /              \r\n";
		$ascii .= "\___/ /_/|_|  \__/  \___/ /_/ /_/ \__,_/   \___/ /_/               \r\n";
		$ascii .= "--------------------------------------------------------           \r\n\n";
		
		return $ascii;

	}

}
