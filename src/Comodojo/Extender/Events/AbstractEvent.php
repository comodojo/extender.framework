<?php namespace Comodojo\Extender\Events;

use \Comodojo\Foundation\Timing\TimeTrait;
use \League\Event\AbstractEvent as LeagueAbstractEvent;

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


abstract class AbstractEvent extends LeagueAbstractEvent {

    use TimeTrait;

    private $name;

    public function __construct($name) {

        $this->setTime();

        $this->name = $name;

    }

    public function getName() {

        return $this->name;

    }

}
