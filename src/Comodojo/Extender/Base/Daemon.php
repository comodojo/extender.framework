<?php namespace Comodojo\Extender\Base;

use \Comodojo\Extender\Components\PidLock;
use \Comodojo\Extender\Components\RunLock;
use \Comodojo\Extender\Events\DaemonEvent;
use \Comodojo\Extender\Listeners\PauseDaemon;
use \Comodojo\Extender\Listeners\ResumeDaemon;
use \Comodojo\Extender\Utils\Checks;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Cache\Cache;
use \Comodojo\Dispatcher\Components\EventsManager;
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

abstract class Daemon extends Process {

    /**
     * @todo exit condition if not in command line
     */
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        EventsManager $events,
        $looptime = 1,
        $niceness = null)
    {

        if ( !Checks::signals() ) {
            throw new Exception("Missing pcntl fork");
        }

        parent::__construct($configuration, $logger, $events, $niceness);

        $this->looptime = self::getLoopTime($looptime);

        $this->loopcount = 0;
        $this->loopactive = true;
        $this->loopelapsed = 0;
        $this->cleanup = true;

        $lockfile = $this->configuration->get('pid-file');
        $runfile = $this->configuration->get('run-file');

        $this->pidlock = new PidLock($this->pid, $lockfile);

        $this->runlock = new RunLock($runfile);

        // attach signal handlers
        $this->events->subscribe('extender.signal.'.SIGTSTP, '\Comodojo\Extender\Listeners\PauseDaemon');
        $this->events->subscribe('extender.signal.'.SIGCONT, '\Comodojo\Extender\Listeners\ResumeDaemon');
        $this->events->subscribe('extender.signal.'.SIGINT, '\Comodojo\Extender\Listeners\StopDaemon');
        $this->events->subscribe('extender.signal.'.SIGTERM, '\Comodojo\Extender\Listeners\StopDaemon');

        // notify that everything is ready
        $this->logger->info("Daemon ready (pid: ".$this->pid.")");

    }

    abstract public function loop();

    public function daemonize() {

        $pid = pcntl_fork();

        if ( $pid == -1 ) {
            $this->logger->error('Could not create daemon (fork error)');
            $this->cleanup = false;
            $this->end(1);
        }

        if ( $pid ) {
            $this->logger->info("Daemon created with pid $pid");
            $this->cleanup = false;
            $this->end(0);
        }

        // become a session leader
        posix_setsid();

        // autostart daemon
        $this->start();

    }

    public function start() {

        // create lockfiles
        $this->runlock->lock();
        $this->pidlock->lock();

        $this->logger->notice("Starting daemon (looping each ".$this->looptime." secs, pid: ".$this->pid.")");

        $this->events->emit( new DaemonEvent('start', $this) );

        while ($this->loopactive) {

            $start = microtime(true);

            pcntl_signal_dispatch();

            $this->events->emit( new DaemonEvent('preloop', $this) );

            if ( $this->runlock->check() && $this->loopactive) {

                $this->events->emit( new DaemonEvent('loopstart', $this) );

                $this->loop();

                $this->events->emit( new DaemonEvent('loopstop', $this) );

                $this->loopcount++;

            }

            $this->events->emit( new DaemonEvent('postloop', $this) );

            $this->loopelapsed = (microtime(true) - $start);

            $lefttime = $this->looptime - $this->loopelapsed;

            if ( $lefttime > 0 ) usleep($lefttime * 1000000);

        }

        $this->logger->notice("Stopping daemon (pid: ".$this->pid.")");

        $this->events->emit( new DaemonEvent('stop', $this) );

        $this->end(0);

    }

    public function stop() {

        // just in case daemon will not execute the stop order
        $this->loopactive = false;
        $this->end(0);

    }

    public function pause() {

        return $this->runlock->pause();

    }

    public function resume() {

        return $this->runlock->resume();

    }

    public function isLooping() {

        $this->runlock->check();

    }

    public function shutdown() {

        // release lockfiles
        if ( $this->cleanup ) {
            $this->runlock->release();
            $this->pidlock->release();
        }

    }

    public static function getLoopTime($looptime) {

        return filter_var($looptime, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 1,
                'min_range' => 1
            )
        ));

    }

}
