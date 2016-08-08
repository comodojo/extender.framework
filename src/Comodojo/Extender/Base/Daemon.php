<?php namespace Comodojo\Extender\Base;

use \Comodojo\Extender\Components\PidLock;
use \Comodojo\Extender\Components\Signaling;
use \Comodojo\Extender\Components\Niceness;
use \Comodojo\Dispatcher\Components\Configuration;
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

    private $pidlock;
    
    private $looptime;

    /**
     * @todo exit condition if not in command line
     */
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Emitter $events,
        $looptime = 1,
        $niceness = null)
    {
        
        parent::__construct($configuration, $logger, $events, $niceness);
        
        $this->looptime = self::getLoopTime($looptime);
        
        $lockfile = $this->configuration->get('pid-file');
        
        $this->pidlock = new PidLock($this->pid, $lockfile);
        
        $this->pidlock->lock();

    }

    abstract public function loop();

    public function start() {
        
        if ( $this->daemon ) {

            $this->logger->notice("Running process (pid: ".$this->pid.") in daemon mode");

            while (true) {

                if ( $this->running ) $this->loop();

                sleep($looptime);

            }

        } else {

            $this->logger->notice("Running process (pid: ".$this->pid.")");

            $this->loop();

        }
        
    }
    
    public function pause() {
        
        $this->logger->notice("Pausing process (pid: ".$this->pid.")");
        
        $this->running = false;
        
        return $this;
        
    }
    
    public function resume() {
        
        $this->logger->notice("Resuming process (pid: ".$this->pid.")");
        
        $this->running = true;
        
        return $this;
        
    }

    public function shutdown() {

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
