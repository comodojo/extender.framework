<?php namespace Comodojo\Extender\Jobs;

use \Comodojo\Extender\Components\Parameters;
use \Comodojo\Dispatcher\Components\DataAccess as DataAccessTrait;

/**
 * Job object
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

class Job {

    use DataAccessTrait;
    
    public function __construct($name, $id, $task, $class, $parameters=array()) {
        
        $this->name = $name;
        $this->id = $id;
        $this->task = $task;
        $this->class = $class;
        $this->parameters = new Parameters($parameters);
        
        $this->uid = self::getUid();
        
    }
    
    /**
     * Get a job unique identifier
     * 
     * @return  string
     */
    private static function getUid() {

        return md5(uniqid(rand(), true), 0);

    }
    
}
