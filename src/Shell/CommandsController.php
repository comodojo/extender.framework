<?php namespace Comodojo\Extender\Shell;

use \Comodojo\Exception\ShellException;
use \Exception;
use \Console_CommandLine;
use \Console_CommandLine_Exception;
use \Monolog\Logger;
use \Comodojo\Extender\TasksTable;
use \Comodojo\Extender\Command\CommandInterface;

/**
 * The commands controller
 *
 * It process command from ConsoleCommandline
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class CommandsController {

    private $command_classes = array();
    
    private $logger = null;

    private $logger = null;

    public function __construct(Console_CommandLine $parser, Logger $logger) {

        $this->logger = $logger;

        $this->parser = $parser;

    }

    public function addCommand($command, $parameters) {

        if ( !isset($parameters["class"]) || class_exists($parameters["class"]) ) {

            $this->logger->warning("Skipping command due to invalid definition", array(
                "NAME"       => $command,
                "PARAMETERS" => $parameters
            ));

        } else {
            
            $this->command_classes[$command] = $parameters["class"];

            $params = array();

            if ( array_key_exists('description', $parameters) ) $params['description'] = $parameters['description'];
            if ( array_key_exists('aliases', $parameters) && is_array($parameters['aliases']) ) $params['aliases'] = $parameters['aliases'];

            $command = $parser->addCommand($command, $params);

            if ( array_key_exists('options', $parameters) && is_array($parameters['options']) ) {

                foreach ( $parameters['options'] as $option => $option_parameters ) {
                    
                    $command->addOption($option, $option_parameters);

                }

            }

            if ( array_key_exists('arguments', $parameters) && is_array($parameters['arguments']) ) {

                foreach ( $parameters['arguments'] as $argument => $argument_parameters ) {
                    
                    $command->addArgument($argument, $argument_parameters);

                }

            }

        }

    }

    /**
     * Execute command
     *
     * @param   string          $command   Command to execute
     * @param   array           $options   Options provided
     * @param   array           $args      Arguments provided
     * @param   Console_Color2  $color     Injected Console_Color2 instance
     * @param   array           $tasks     Array of available tasks
     *
     * @return  string
     */
    public function executeCommand($command_name, $options, $args, $color, TasksTable $tasks) {

        if ( array_key_exists($command_name, $this->command_classes) ) {

            $command_class = $this->command_classes[$command_name];

        } else {

            throw new ShellException("Command ".$command_name." not defined");

        }

        try {
            
            $command = new $command_class();

            if ( ($command instanceof CommandInterface) === false ) throw new ShellException("Command ".$command_name." is not compatible with econtrol");

            $command->setOptions($options);

            $command->setArguments($args);

            $command->setColor($color);

            $command->setTasks($tasks);

            $return = $command->execute();

        } catch (ShellException $se) {
            
            throw $se;

        } catch (Exception $e) {
            
            throw $e;

        }

        return $return;

    }

    public static function loadCommands(Console_CommandLine $parser, Logger $logger) {

        $controller = new CommandsController($parser, $logger);

        if ( is_readable(EXTENDER_COMMANDS_CONFIG) ) {

            $commands = Spyc::YAMLLoad(EXTENDER_COMMANDS_CONFIG);

            foreach ($commands as $command => $parameters) {
                
                $controller->addCommand($command, $parameters);

            }

        }

        return $controller;

    }

}
