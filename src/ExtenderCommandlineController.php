<?php namespace Comodojo\Extender;

use \Console_CommandLine;
use \Console_CommandLine_Exception;
use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Shell\CommandsController;
use \Console_Color2;
use \Exception;

class ExtenderCommandlineController {

	private $parser = null;

	private $color = null;

	private $tasks = array();

	private $commands = array();

	public function __construct() {

		date_default_timezone_set(defined('EXTENDER_TIMEZONE') ? EXTENDER_TIMEZONE : 'Europe/Rome');

		$this->parser = new Console_CommandLine(array(
		    'description' => Version::getDescription(),
		    'version'     => Version::getVersion()
		));

		$this->color = new Console_Color2();

	}

	final public function addTask($name, $target, $description, $class=null, $relative=true) {

		if ( empty($name) OR empty($target) ) {

			echo $this->color->convert("\n%ySkipping task ".$name." due to invalid definition%n\n");

			// $this->logger->warning("Skipping task due to invalid definition", array(
			// 	"NAME"		 =>	$name,
			// 	"TARGET"	 =>	$target,
			// 	"DESCRIPTION"=> $description,
			// 	"RELATIVE"	 => $relative
			// ));

			return false;

		}

		$this->tasks[$name] = array(
			"description" => $description,
			"target"	  => $relative ? EXTENDER_TASK_FOLDER.$target : $target,
			"class"		  => empty($class) ? preg_replace('/\\.[^.\\s]{3,4}$/', '', $target) : $class
		);

		return true;

	}

	final public function addCommand($command, $parameters=array()) {

		if ( empty($command) or !is_array($parameters) ) {

			echo $this->color->convert("\n%ySkipping command ".$command." due to invalid definition%n\n");

			return false;

		}

		$this->commands[$command] = $parameters;

		return true;

	} 

	public function process() {

		CommandsController::addCommands($this->parser, $this->commands);

		try {

			$check_constants = self::checkConstants();

			if ( $check_constants !== true ) throw new ShellException($check_constants);

			if ( self::extenderIsRunningFromCli() === false ) throw new ShellException("Extender Shell runs only in php-cli, exiting.");

		    $result = $this->parser->parse();
		    
		    if ( empty($result->command_name) ) {

		    	$this->parser->displayUsage();

		    	exit(0);

		    }

		    $return = CommandsController::executeCommand($result->command_name, $result->command->options, $result->command->args, $this->color, $this->tasks);

		} catch (Console_CommandLine_Exception $ce) {

			$this->parser->displayError( $this->color->convert("\n\n%y".$ce->getMessage()."%n\n") );

		    exit(1);

		} catch (ShellException $se) {

			$this->parser->displayError( $this->color->convert("\n\n%R".$se->getMessage()."%n\n") );

		    exit(1);

		} catch (Exception $e) {

		    $this->parser->displayError( $this->color->convert("\n\n%r".$e->getMessage()."%n\n") );

		    exit(1);

		}

		echo "\n".$return."\n\n";

		exit(0);

	}

	private static function extenderIsRunningFromCli() {

		return php_sapi_name() === 'cli';

	}

	private static function checkConstants() {

		if ( !defined("EXTENDER_DATABASE_MODEL") ) return "Invalid database model. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_MODEL.";
		if ( !defined("EXTENDER_DATABASE_HOST") ) return "Unknown database host. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_HOST.";
		if ( !defined("EXTENDER_DATABASE_PORT") ) return "Invalid database port. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_PORT.";
		if ( !defined("EXTENDER_DATABASE_NAME") ) return "Invalid database name. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_NAME.";
		if ( !defined("EXTENDER_DATABASE_USER") ) return "Invalid database user. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_USER.";
		if ( !defined("EXTENDER_DATABASE_PASS") ) return "Invalid database password. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_PASS.";
		if ( !defined("EXTENDER_DATABASE_PREFIX") ) return "Invalid database table prefix. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_PREFIX.";
		if ( !defined("EXTENDER_DATABASE_TABLE_JOBS") ) return "Invalid database jobs' table. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_TABLE_JOBS.";
		if ( !defined("EXTENDER_DATABASE_TABLE_WORKLOGS") ) return "Invalid database worklogs' table. \n\n Please check your extender configuration and define constant: EXTENDER_DATABASE_TABLE_WORKLOGS.";
		if ( !defined("EXTENDER_TASK_FOLDER") ) return "Invalid tasks' folder. \n\n Please check your extender configuration and define constant: EXTENDER_TASK_FOLDER.";

		return true;

	}

}
