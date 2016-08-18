<?php namespace Comodojo\Extender\Components;

/**
 * @package     Comodojo Framework
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
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

trait ArrayAccess {

    /**
     * Return the value at index
     *
     * @return string $index The offset
     */
     public function offsetGet($index) {

         return $this->data[$index];

     }

     /**
     * Assigns a value to index offset
     *
     * @param string $index The offset to assign the value to
     * @param mixed  $value The value to set
     */
     public function offsetSet($index, $value) {

         $this->data[$index] = $value;

     }

     /**
     * Unsets an index
     *
     * @param string $index The offset to unset
     */
     public function offsetUnset($index) {

         unset($this->data[$index]);

     }

     /**
     * Check if an index exists
     *
     * @param string $index Offset
     * @return boolean
     */
     public function offsetExists($index) {

         return $this->offsetGet($index) !== null;

     }

}
