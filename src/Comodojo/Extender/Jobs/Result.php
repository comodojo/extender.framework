<?php namespace Comodojo\Extender\Jobs;

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

class Result {

    use DataAccessTrait;
    
    public function __construct($process_output) {
        
        $this->pid = $process_output[0];
        $this->name = $process_output[1];
        $this->success = $process_output[2];
        $this->start = $process_output[3];
        $this->end = $process_output[4];
        $this->result = $process_output[5];
        $this->id = $process_output[6];
        $this->wid = $process_output[7];
        
    }
    
    public function raw() {
        
        return array_values($this->data);
        
    }
    
}
