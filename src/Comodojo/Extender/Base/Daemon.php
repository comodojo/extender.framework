<?php namespace Comodojo\Extender\Base;

use \Comodojo\Extender\Components\PidLock;
use \Comodojo\Dispatcher\Events;
use \Monolog\Logger;

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

abstract class Daemon {

    protected $pid;

    protected $logger;

    protected $events;

    protected $pidlock;

    /**
     * @todo exit condition if not in command line
     */
    public function __construct(Logger $logger, Events $events, $niceness = null) {

        $this->logger = $logger;

        $this->events = $events;

        $this->pidlock = new PidLock();

        $this->pid = posix_getpid();

        if ( is_int($niceness) ) $this->adjustNiceness($niceness);

        $this->registerSignals();

        self::lock($this->pid);

    }

    abstract public function loop($time);

    public function shutdown() {

        self::release();

    }

    /**
     * Change parent process priority according to EXTENDER_NICENESS
     *
     */
    final public function adjustNiceness($niceness) {

        $nice = proc_nice($niceness);

        if ( $nice === false ) $this->logger->warning("Unable to set parent process niceness to ".$niceness);

    }

    /**
     * Register signals
     */
    final public function registerSignals() {

        $pluggable_signals = array(
            SIGHUP, SIGCHLD, SIGUSR2, SIGILL, SIGTRAP, SIGABRT, SIGIOT, SIGBUS, SIGFPE,
            SIGSEGV, SIGPIPE, SIGALRM, SIGTTIN, SIGTTOU, SIGURG, SIGXCPU, SIGXFSZ,
            SIGVTALRM, SIGPROF, SIGWINCH, SIGIO, SIGSYS, SIGBABY
        );

        if ( defined('SIGPOLL') )   $pluggable_signals[] = SIGPOLL;
        if ( defined('SIGPWR') )    $pluggable_signals[] = SIGPWR;
        if ( defined('SIGSTKFLT') ) $pluggable_signals[] = SIGSTKFLT;

        // register supported signals

        pcntl_signal(SIGTERM, array($this, 'sigTermHandler'));

        pcntl_signal(SIGINT, array($this, 'sigTermHandler'));

        pcntl_signal(SIGTSTP, array($this, 'sigStopHandler'));

        pcntl_signal(SIGCONT, array($this, 'sigContHandler'));

        //pcntl_signal(SIGUSR1, array($this,'sigUsr1Handler'));

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
    final public function sigTermHandler() {

        if ( $this->pid == posix_getpid() ) {

            $this->logger->info("Received TERM signal, shutting down extender gracefully");

            $this->runner->killAll($this->parent_pid);

            self::end(1);

        }

    }

    /**
     * The sigStop handler.
     *
     * It just pauses extender execution
     */
    final public function sigStopHandler() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received STOP signal, pausing extender");

            $this->paused = true;

        }

    }

    /**
     * The sigCont handler.
     *
     * It just resume extender execution
     */
    final public function sigContHandler() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received CONT signal, resuming extender");

            $this->paused = false;

        }

    }

    /**
     * The generig signal handler.
     *
     * It can be used to handle custom signals
     */
    final public function genericSignalHandler($signal) {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received ".$signal." signal, firing associated event(s)");

            $this->events->fire("extender.signal.".$signal, "VOID", $this);

        }

    }

}
