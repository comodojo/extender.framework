<?php namespace Comodojo\Extender\Base;

use \Comodojo\Extender\Components\Niceness;
use \Comodojo\Dispatcher\Components\DataAccess as DataAccessTrait;
use \Comodojo\Extender\Events\SignalEvent;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \League\Event\Emitter;
use \Psr\Log\LoggerInterface;
use \Exception;

/**
 * Basic signaling for extender components
 *
 * @package     Comodojo Framework
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

abstract class Process {

    use DataAccessTrait;
    use TimestampTrait;

    /**
     * @todo exit condition if not in command line
     */
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Emitter $events,
        $niceness = null)
    {

        // get current PID and timestamp

        $this->pid = $this->getPid();

        $this->setTimestamp();

        // init main components

        $this->configuration = $configuration;

        $this->logger = $logger;

        $this->events = $events;

        // adjust process niceness

        $this->niceness = new Niceness($this->logger);

        $this->niceness->set($niceness);

        $this->registerSignals();

    }

    abstract public function shutdown();

    /**
     * Register signals
     *
     */
    protected function registerSignals() {

        $pluggable_signals = array(
            SIGHUP, SIGCHLD, SIGUSR1, SIGUSR2, SIGILL, SIGTRAP, SIGABRT, SIGIOT,
            SIGBUS, SIGFPE, SIGSEGV, SIGPIPE, SIGALRM, SIGTTIN, SIGTTOU, SIGURG,
            SIGXCPU, SIGXFSZ, SIGVTALRM, SIGPROF, SIGWINCH, SIGIO, SIGSYS, SIGBABY,
            SIGTSTP, SIGCONT
        );

        if ( defined('SIGPOLL') )   $pluggable_signals[] = SIGPOLL;
        if ( defined('SIGPWR') )    $pluggable_signals[] = SIGPWR;
        if ( defined('SIGSTKFLT') ) $pluggable_signals[] = SIGSTKFLT;

        // register supported signals

        pcntl_signal(SIGTERM, array($this, 'sigTermHandler'));

        pcntl_signal(SIGINT, array($this, 'sigIntHandler'));

        // pcntl_signal(SIGTSTP, array($this, 'sigStopHandler'));

        // pcntl_signal(SIGCONT, array($this, 'sigContHandler'));

        // register pluggable signals

        foreach ( $pluggable_signals as $signal ) {

            pcntl_signal($signal, array($this, 'genericSignalHandler'));

        }

        // register shutdown function

        register_shutdown_function(array($this, 'shutdown'));

    }

    /**
     * The sigTerm handler.
     *
     * It kills everything and then exit with status 1
     */
    public function sigIntHandler($signal) {

        if ( $this->pid == $this->getPid() ) {

            $this->logger->info("Received TERM signal, shutting down process gracefully");

            $this->events->emit( new SignalEvent($signal, $this) );

            $this->end(0);

        }

    }

    /**
     * The sigTerm handler.
     *
     * It kills everything and then exit with status 1
     */
    public function sigTermHandler($signal) {

        if ( $this->pid == $this->getPid() ) {

            $this->logger->info("Received TERM signal, shutting down process");

            $this->events->emit( new SignalEvent($signal, $this) );

            $this->end(1);

        }

    }

    /**
     * The generig signal handler.
     *
     * It can be used to handle custom signals
     */
    public function genericSignalHandler($signal) {

        if ( $this->pid == $this->getPid() ) {

            $this->logger->info("Received $signal signal, firing associated event(s)");

            $this->events->emit( new SignalEvent($signal, $this) );

        }

    }

    /**
     * @param integer $return_code
     */
    protected function end($return_code) {

        if ( $this->configuration->get('is-test') === true ) {

            if ( $return_code === 1 ) throw new Exception("Test Exception");

            else return $return_code;

        } else {

            exit($return_code);

        }

    }

    private function getPid() {

        return posix_getpid();

    }

}
