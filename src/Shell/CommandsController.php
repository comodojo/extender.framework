<?php namespace Comodojo\Extender\Shell;

use \Comodojo\Exception\ShellException;

class CommandsController {
	
	static final public function addCommands($parser, $commands) {

		foreach ($commands as $command => $parameters) {
			
			$params = array();

			if ( array_key_exists('description', $parameters) ) $params['description'] = $parameters['description'];
			if ( array_key_exists('aliases', $parameters) AND is_array($parameters['aliases']) ) $params['aliases'] = $parameters['aliases'];

			$command = $parser->addCommand($command, $params);

			if ( array_key_exists('options', $parameters) AND is_array($parameters['options']) ) {

				foreach ($parameters['options'] as $option => $option_parameters) {
					
					$command->addOption($option, $option_parameters);

				}

			}

			if ( array_key_exists('arguments', $parameters) AND is_array($parameters['arguments']) ) {

				foreach ($parameters['arguments'] as $argument => $argument_parameters) {
					
					$command->addArgument($argument, $argument_parameters);

				}

			}

		}

	}

	static final public function executeCommand($command, $options, $args, $color, $tasks) {

		$command_class = "\\Comodojo\\Extender\\Shell\\Commands\\".$command;

		try {
			
			$command = new $command_class();

			$command->setOptions($options);

			$command->setArgs($args);

			$command->setColor($color);

			$command->setTasks($tasks);

			$return = $command->exec();

		} catch (ShellException $se) {
			
			throw $se;

		} catch (Exception $e) {
			
			throw $e;

		}

		// $return = "\nCommand: ".$command."\n";
		// $return .= "\nOptions: ".var_export($options, true)."\n";
		// $return .= "\nArgs: ".var_export($args, true)."\n";

		return $return;

	}

}
