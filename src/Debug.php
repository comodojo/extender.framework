<?php namespace Comodojo\Extender;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;

/**
 * Init the monolog logger/debugger
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

class Debug {

    /**
     * Monolog instance
     *
     * @var     Object
     */
    private $logger = null;

    /**
     * Verbose mode pointer
     *
     * @var     bool
     */
    private $verbose = false;

    /**
     * Console_Color2 instance
     *
     * @var     Object
     */
    private $color = null;

    /**
     * Debug class contructor method
     *
     * @param   bool    $verbose    Turn on/off verbose mode
     * @param   Object  $color      Console_Color2 injected object
     */
    final public function __construct($verbose, $color) {

        $enabled = defined('EXTENDER_LOG_ENABLED') ? filter_var(EXTENDER_LOG_ENABLED, FILTER_VALIDATE_BOOLEAN) : false;

        $name = defined('EXTENDER_LOG_NAME') ? EXTENDER_LOG_NAME : 'extender-default';

        $level = $this->getLevel( defined('EXTENDER_LOG_LEVEL') ? EXTENDER_LOG_LEVEL : 'DEBUG' );

        $target = defined('EXTENDER_LOG_TARGET') ? EXTENDER_LOG_TARGET : null;

        $this->logger = new Logger($name);

        if ( $enabled ) {

            $handler = is_null($target) ? new ErrorLogHandler() : new StreamHandler( defined('EXTENDER_LOG_FOLDER') ? EXTENDER_LOG_FOLDER.$target : $target, $level);

        }
        else {

            $handler = new NullHandler($level);

        }

        $this->logger->pushHandler($handler);

        $this->verbose = filter_var($verbose, FILTER_VALIDATE_BOOLEAN);

        $this->color = $color;

    }

    /**
     * Get monolog instance
     *
     * @return \Monolog\Logger
     */
    final public function getLogger() {

        return $this->logger;

    }

    /**
     * Raise an INFO message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function info($message, array $context = array()) {

        $log = $this->logger->addInfo($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%g\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%g".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise a NOTICE message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function notice($message, array $context = array()) {

        $log = $this->logger->addNotice($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%U\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%U".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise a WARNING message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function warning($message, array $context = array()) {

        $log = $this->logger->addWarning($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%Y\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%Y".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise an ERROR message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function error($message, array $context = array()) {

        $log = $this->logger->addError($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%r\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%r".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise a CRITICAL message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function critical($message, array $context = array()) {

        $log = $this->logger->addCritical($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%r\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%r".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise an ALERT message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function alert($message, array $context = array()) {

        $log = $this->logger->addAlert($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%r\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%r".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise an EMERGENCY message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function emergency($message, array $context = array()) {

        $log = $this->logger->addEmergency($message, $context);

        if ( $this->verbose ) {

            print $this->color->convert("%r\n".$message."%n\n");

            if ( !empty($context) ) print $this->color->convert("%r".var_export($context, true)."%n\n");

        }

        return $log;

    }

    /**
     * Raise a DEBUG message
     *
     * @param   string    $message    Message text
     * @param   array     $context    (optional) array of included informations
     *
     * @return  bool
     */
    public function debug($message, array $context = array()) {

        $log = $this->logger->addDebug($message, $context);

        if ( $this->verbose ) {

            print "\n".$message."\n";

            if ( !empty($context) ) print var_export($context, true)."\n";

        }

        return $log;

    }

    /**
     * Map provided log level to monolog level code
     *
     * @param   string    $level    Debug/log level
     *
     * @return  integer
     */
    private function getLevel($level) {

        switch (strtoupper($level)) {

            case 'INFO':
                $logger_level = Logger::INFO;
                break;

            case 'NOTICE':
                $logger_level = Logger::NOTICE;
                break;

            case 'WARNING':
                $logger_level = Logger::WARNING;
                break;

            case 'ERROR':
                $logger_level = Logger::ERROR;
                break;

            case 'CRITICAL':
                $logger_level = Logger::CRITICAL;
                break;

            case 'ALERT':
                $logger_level = Logger::ALERT;
                break;

            case 'EMERGENCY':
                $logger_level = Logger::EMERGENCY;
                break;

            case 'DEBUG':
            default:
                $logger_level = Logger::DEBUG;
                break;

        }

        return $logger_level;

    }
    
}