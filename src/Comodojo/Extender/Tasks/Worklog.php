<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Dispatcher\Components\Configuration;
use \Psr\Log\LoggerInterface;

/**
 * Task object
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

class Worklog {
    
    private $configuration;
    private $logger;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger
    ) {
        
        // Setup task
        $this->configuration = $configuration;
        $this->logger = $logger;

    }
    
    public function create() {
        
    }
    
    public function close() {
        
    }
    
}