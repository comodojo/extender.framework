<?php namespace Comodojo\Extender\Components;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Validation\DataFilter;
use \Exception;

/**
 * Basic IPC implementation
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

class Ipc {

    const READER = 0;

    const WRITER = 1;

    private $bytes;

    private $ipc = [];

    public function __construct(Configuration $configuration) {

        $this->bytes = DataFilter::filterInteger(
            $configuration->get('child-max-result-bytes'),
            1024,
            PHP_INT_MAX,
            16384
        );

    }

    public function init($uid) {

        $this->ipc[$uid] = [];

        $socket = socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $this->ipc[$uid]);

        if ( $socket == false ) throw new Exception("Socket error: ".socket_strerror(socket_last_error()));

        return $socket;

    }

    public function read($uid) {

        $reader = $this->ipc[$uid][self::READER];

        $result = socket_read($reader, $this->bytes, PHP_BINARY_READ);

        if ( $result === false ) throw new Exception("Socket error: ".socket_strerror(socket_last_error($reader)));

        return $result;

    }

    public function write($uid, $data) {

        $writer = $this->ipc[$uid][self::WRITER];

        $result = socket_write($writer, $data, strlen($data));

        if ( $result === false ) throw new Exception("Socket error: ".socket_strerror(socket_last_error($writer)));

        return $result;

    }

    public function close($uid, $handler) {

        socket_close($this->ipc[$uid][$handler]);

    }

    public function hang($uid) {

        socket_close($this->ipc[$uid][self::READER]);
        socket_close($this->ipc[$uid][self::WRITER]);

    }

    public function free() {

        $this->ipc = [];

    }

}
