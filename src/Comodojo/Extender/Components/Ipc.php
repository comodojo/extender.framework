<?php namespace Comodojo\Extender\Components;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Validation\DataFilter;
use \Exception;

/**
 * @package     Comodojo Extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
