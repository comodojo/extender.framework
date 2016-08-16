<?php namespace Comodojo\Extender\Jobs;

use \Comodojo\Extender\Components\Niceness;
use \Comodojo\Dispatcher\Components\Configuration;
use \Psr\Log\LoggerInterface;

/**
 * Job runner
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

class Runner {

    protected $configuration;
    
    protected $logger;
    
    protected $niceness;
    
    protected $lagger_timeout = 5;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {
        
        // init components
        $this->configuration = $configuration;
        
        $this->logger = $logger;
        
        $this->niceness = new Niceness($this->logger);
        
        // retrieve parameters
        
        $this->lagger_timeout = self::getLaggerTimeout($this->configuration);
        
        $this->multithread = $configuration->get('multithread') === true;
        
    }
    
    
    
    
    
    
    
    private static function getLaggerTimeout(Configuration $configuration) {
        
        return filter_var($configuration->get('child-lagger-timeout'), FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 5,
                'min_range' => 0
            )
        ));
        
    }
    
}
