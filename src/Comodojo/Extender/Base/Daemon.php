<?php namespace Comodojo\Extender\Base;

use \Comodojo\Extender\Components\PidLock;
use \Comodojo\Extender\Components\RunLock;
use \Comodojo\Extender\Components\Niceness;
use \Comodojo\Extender\Events\DaemonEvent;
use \Comodojo\Extender\Listeners\PauseDaemon;
use \Comodojo\Extender\Listeners\ResumeDaemon;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Cache\Cache;
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

abstract class Daemon extends Process {

    /**
     * @todo exit condition if not in command line
     */
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Emitter $events,
        Cache $cache,
        $looptime = 1,
        $niceness = null)
    {

        parent::__construct($configuration, $logger, $events, $niceness);

        $this->looptime = self::getLoopTime($looptime);
        
        $this->loopcount = 0;

        $lockfile = $this->configuration->get('pid-file');
        $runfile = $this->configuration->get('run-file');

        $this->pidlock = new PidLock($this->pid, $lockfile);
        
        $this->runlock = new RunLock($runfile);

        // create lockfiles
        $this->runlock->lock();
        $this->pidlock->lock();
        
        // attach signal handlers
        $this->events->subscribe('extender.signal.'.SIGTSTP, '\Comodojo\Extender\Listeners\PauseDaemon');
        $this->events->subscribe('extender.signal.'.SIGCONT, '\Comodojo\Extender\Listeners\ResumeDaemon');
        
        // notify that everything is ready
        $this->logger->info("Daemon ready (pid: ".$this->pid.")");

    }

    abstract public function loop();

    public function start() {
        
        $this->logger->notice("Starting daemon (looping each ".$this->looptime." secs, pid: ".$this->pid.")");
        
        $this->events->emit( new DaemonEvent('start', $this) );

        while (true) {
            
            $start = microtime(true);

            pcntl_signal_dispatch();
            
            if ( $this->runlock->check() ) {
                
                $this->events->emit( new DaemonEvent('loopstart', $this) );
                
                $this->loop();
                
                $this->events->emit( new DaemonEvent('loopstop', $this) );
                
            }
            
            $lefttime = $this->looptime - (microtime(true) - $start);
            
            if ( $lefttime > 0 ) usleep($lefttime * 1000);

        }

        $this->logger->notice("Stopping daemon (pid: ".$this->pid.")");
        
        $this->events->emit( new DaemonEvent('stop', $this) );

        $this->end(0);

    }

    public function shutdown() {
        
        // release lockfiles
        $this->runlock->release();
        $this->pidlock->release();

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
