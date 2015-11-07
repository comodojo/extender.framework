<?php namespace Comodojo\Extender;

use \Console_CommandLine;
use \Console_CommandLine_Exception;
use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Shell\CommandsController;
use \Comodojo\Extender\TasksTable;
use \Console_Color2;
use \Exception;

/**
 * Extender command line controller (econtrol)
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

class Econtrol {

    /**
     * Console_CommandLine parser instance
     *
     * @var \Console_CommandLine
     */
    private $parser = null;

    /**
     * Console_Color2 instance
     *
     * @var \Console_Color2
     */
    private $color = null;

    /**
     * Array of registered/declared tasks
     *
     * @var array
     */
    private $tasks = null;

    private $controller = null;

    /**
     * econtrol constructor
     *
     */
    public function __construct() {

        // check if econtrol is running from cli

        if ( Checks::cli() === false ) {

            echo "Econtrol runs only in php-cli, exiting";

            self::end(1);

        }

        if ( defined('EXTENDER_TIMEZONE') ) date_default_timezone_set(EXTENDER_TIMEZONE);

        $this->parser = new Console_CommandLine(array(
            'description' => Version::getDescription(),
            'version'     => Version::getVersion()
        ));

        $this->color = new Console_Color2();

        $this->tasks = TasksTable::loadTasks($this->logger);

        $this->controller = CommandsController::loadCommands($this->parser, $this->logger);

    }

    /**
     * Process command
     *
     * @return  string
     */
    public function process() {

        try {

            $check_constants = Checks::constants();

            if ( $check_constants !== true ) throw new ShellException($check_constants);

            $result = $this->parser->parse();
            
            if ( empty($result->command_name) ) {

                $this->parser->displayUsage();

                self::end(0);

            }

            $return = $this->controller->executeCommand($result->command_name, $result->command->options, $result->command->args, $this->color, $this->tasks);

        } catch (Console_CommandLine_Exception $ce) {

            $this->parser->displayError($this->color->convert("\n\n%y".$ce->getMessage()."%n\n"));

            self::end(1);

        } catch (ShellException $se) {

            $this->parser->displayError($this->color->convert("\n\n%R".$se->getMessage()."%n\n"));

            self::end(1);

        } catch (Exception $e) {

            $this->parser->displayError($this->color->convert("\n\n%r".$e->getMessage()."%n\n"));

            self::end(1);

        }

        echo "\n".$return."\n\n";

        self::end(0);

    }

    /**
     * @param integer $returnCode
     */
    private static function end($returnCode) {

        if ( defined('EXTENDER_PHPUNIT_TEST') && @constant('EXTENDER_PHPUNIT_TEST') === true ) {

            if ( $returnCode === 1 ) throw new Exception("PHPUnit Test Exception");
            
            else return $returnCode;

        } else {

            exit($returnCode);

        }

    }

}
