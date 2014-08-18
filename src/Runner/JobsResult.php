<?php namespace Comodojo\Extender\Runner;

/**
 * Jobs result object
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
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

class JobsResult {

    /**
     * Tasks database (a simple array!).
     *
     * @var     array
     */
    private $completed_processes = array();

    final public function __construct($processes) {

        $this->completed_processes = $processes;

    }

    /**
     * Get raw results
     *
     * @param   bool      $raw    If true, results will be returned as NUM array, if false as an ASSOC array
     *
     * @return  array()
     */
    final public function get($raw=true) {

        if ( $raw === true ) return $this->completed_processes;

        else return self::convert($this->completed_processes);

    }

    /**
     * Convert result into ASSOC array
     *
     * @param   array     $processes     Array of process results
     *
     * @return  string
     */
    static private function convert($processes) {

        $assoc_processes = array();

        foreach ($processes as $process) {
            
            array_push($assoc_processes, array(
                'pid'    =>  $process[0],
                'name'   =>  $process[1],
                'success'=>  $process[2],
                'start'  =>  $process[3],
                'end'    =>  $process[4],
                'result' =>  $process[5],
                'id'     =>  $process[6]//,
                //'uid'    =>  $process[7]
            ));

        }

        return $assoc_processes;
        
    }

}
