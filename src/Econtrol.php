<?php namespace Comodojo\Extender;

use \Console_CommandLine;
use \Console_CommandLine_Exception;
use \Comodojo\Exception\ShellException;
use \Comodojo\Extender\Shell\Controller;
use \Comodojo\Extender\TasksTable;
use \Comodojo\Extender\Log\EcontrolLogger;
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
     * Local taskstable
     *
     * @var \comodojo\Extender\TasksTable
     */
    private $tasks = null;

    /**
     * Commands controller
     *
     * @var \Comodojo\Extender\Shell\Controller
     */
    private $controller = null;

    /**
     * Commands controller
     *
     * @var \Monolog\Logger;
     */
    private $logger = null;

    /**
     * Output of console parser
     *
     * @var array
     */
    private $command = null;

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

        $this->color = new Console_Color2();

        $this->parser = new Console_CommandLine(array(
            'description' => Version::getDescription(),
            'version'     => Version::getVersion()
        ));

        $this->parser->addOption(
            'verbose',
            array(
                'short_name'  => '-v',
                'long_name'   => '--verbose',
                'description' => 'turn on verbose output',
                'action'      => 'StoreTrue'
            )
        );

        try {

            $check_constants = Checks::constants();

            if ( $check_constants !== true ) throw new ShellException($check_constants);

            $verbose = array_key_exists('v', getopt("v")) ? true : false;

            $this->logger = EcontrolLogger::create($verbose);

            $this->tasks = TasksTable::load($this->logger);

            $this->controller = Controller::load($this->parser, $this->logger);

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

    }

    final public function tasks() {

        return $this->tasks;

    }

    final public function controller() {

        return $this->controller;
        
    }

    final public function logger() {

        return $this->logger;
        
    }

    /**
     * Process command
     *
     * @return  string
     */
    public function process() {

        try {

            $this->command = $this->parser->parse();

            if ( empty($this->command->command_name) ) {

                $this->parser->displayUsage();

                self::end(0);

            }

            $return = $this->controller->execute(
                $this->command->command_name,
                $this->command->command->options,
                $this->command->command->args,
                $this->color,
                $this->logger,
                $this->tasks
            );

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

        if ( defined('COMODOJO_PHPUNIT_TEST') && @constant('COMODOJO_PHPUNIT_TEST') === true ) {

            if ( $returnCode === 1 ) throw new Exception("PHPUnit Test Exception");
            
            else return $returnCode;

        } else {

            exit($returnCode);

        }

    }

}
