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

trait Iterator {

    /**
     * Reset the iterator
     */
    public function rewind() {

        reset($this->data);

    }

    /**
     * Get the current element
     *
     * @return mixed
     */
    public function current() {

        return current($this->data);

    }

    /**
     * Return the current key
     *
     * @return string|int
     */
    public function key() {

        return key($this->data);

    }

    /**
     * Move to next element
     */
    public function next() {

        return next($this->data);

    }

    /**
     * Check if element is valid (isset)
     *
     * @return boolean
     */
    public function valid() {

        return isset($this->data[$this->key()]);

    }

}
