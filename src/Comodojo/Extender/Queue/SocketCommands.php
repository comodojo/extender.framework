<?php namespace Comodojo\Extender\Queue;

use \Comodojo\Daemon\Process;
use \Comodojo\Extender\Task\Request;

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

class SocketCommands {

    public function queueAdd(Process $daemon, Request $request = null) {

        return self::getManager($daemon)->add($request);

    }

    public function queueAddBulk(Process $daemon, array $requests = []) {

        return self::getManager($daemon)->addBulk($requests);

    }

    public function queueInfo(Process $daemon, $data = null) {

        $configuration = $daemon->getConfiguration();
        $base_path = $configuration->get('base-path');
        $lock_path = $configuration->get('run-path');
        $lock_file = "$base_path/$lock_path/queue.worker.lock";

        return unserialize(file_get_contents($lock_file));

    }

    protected static function getManager(Process $daemon) {

        return new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

    }

}
