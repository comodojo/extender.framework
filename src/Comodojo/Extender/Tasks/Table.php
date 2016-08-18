<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Extender\Components\ArrayAccess as ArrayAccessTrait;
use \Comodojo\Extender\Components\Iterator as IteratorTrait;
use \Comodojo\Extender\Components\Countable as CountableTrait;
use \Comodojo\Dispatcher\Components\DataAccess as DataAccessTrait;
use \Psr\Log\LoggerInterface;
use \Iterator;
use \ArrayAccess;
use \Countable;

/**
 * @package     Comodojo Dispatcher
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


class Table implements Iterator, ArrayAccess, Countable {

    use ArrayAccessTrait;
    use IteratorTrait;
    use CountableTrait;

    private $data = array();

    private $logger;

    public function __construct(LoggerInterface $logger) {

        $this->logger = $logger;

    }

    public function get($name) {

        if ( array_key_exists($name, $this->data) ) {
            return $this->data[$name];
        }

        return null;

    }

    public function add($name, $class, $description = null) {

        if ( array_key_exists($name, $this->data) ) {
            $this->logger->warning("Skipping duplicate task $name ($class)");
            return false;
        }

        if ( empty($name) || empty($class) ) {
            $this->logger->warning("Skipping invalid task definition", array(
                "NAME"       => $name,
                "CLASS"      => $class,
                "DESCRIPTION"=> $description
            ));
            return false;
        }

        $this->data[$name] = new Task($name, $class, $description);

        return true;

    }

    public function delete($name) {

        if ( array_key_exists($name, $this->data) ) {
            unset($this->data[$name]);
            return true;
        }

        return false;

    }

    public function bulk($tasks) {

        $result = array();

        foreach($tasks as $task) {

            if ( empty($task['name']) || empty($task['class']) ) {

                $this->logger->warning("Skipping invalid task definition", array(
                    "NAME"       => $name,
                    "CLASS"      => $class,
                    "DESCRIPTION"=> $description
                ));
                $result[] = false;

            } else {

                $result[] = $this->add($task['name'], $task['class'], empty($task['description']) ? null : $task['description']);

            }

        }

        return $result;

    }

}
