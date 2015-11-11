<?php namespace Comodojo\Extender\Log;

use Monolog\Logger;
use \Monolog\Handler\AbstractProcessingHandler;
use \Console_Color2;

class ConsoleHandler extends AbstractProcessingHandler {

    private $color = null;

    private static $colors = array(
        100 => '%8',
        200 => '%g',
        250 => '%U',
        300 => '%Y',
        400 => '%r',
        500 => '%R',
        550 => '%m',
        600 => '%M',
    );

    public function __construct($level = Logger::DEBUG, $bubble = true) {

        $this->color = new Console_Color2();

        parent::__construct($level, $bubble);

    }

    protected function write(array $record) {
        
        $level = $record['level'];

        $message = $record['formatted'];

        $context = empty($record['context']) ? null : $record['context'];

        $time = $record['datetime']->format('c');

        $this->toConsole($time, $level, $message, $context);

    }

    private function toConsole($time, $level, $message, $context) {

        print $this->color->convert(static::$colors[$level].$message."%n");

        if ( !empty($context) ) print $this->color->convert(static::$colors[$level].var_export($context, true)."%n\n");

    }

}
