<?php namespace Comodojo\Extender\Socket;

use \Comodojo\Daemon\Process;
use \Comodojo\Extender\Worklog\Manager;
use \Comodojo\Extender\Worklog\Transformer;
use \Comodojo\Foundation\Utils\ArrayOps;
use \League\Fractal\Manager as FractalManager;
use \League\Fractal\Resource\Item;
use \League\Fractal\Resource\Collection;

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

class WorklogCommands {

    public function count(Process $daemon) {

        return self::getManager($daemon)->count();

    }

    public function list(Process $daemon, array $payload = null) {

        if ( empty($payload) ) {

            $data = self::getManager($daemon)->get();

        } else {

            $options = ArrayOps::replaceStrict([
                'limit' => 10,
                'offset' => 0,
                'reverse' => false
            ], $payload);

            $data = self::getManager($daemon)->get(
                [],
                $options['limit'],
                $options['offset'],
                $options['reverse']
            );

        }

        $resource = new Collection($data, new Transformer);
        $fractal = new FractalManager();
        $data = $fractal->createData($resource)->toArray();

        return $data['data'];

    }

    public function byId(Process $daemon, $id) {

        $data = self::getManager($daemon)->getOne(['id' => $id]);

        if ( empty($data) ) return null;

        $resource = new Item($data, new Transformer);
        $fractal = new FractalManager();
        $data = $fractal->createData($resource)->toArray();

        return $data['data'];

    }

    public function byJid(Process $daemon, $jid) {

        $data = self::getManager($daemon)->get(['jid' => $jid]);

        $resource = new Collection($data, new Transformer);
        $fractal = new FractalManager();
        $data = $fractal->createData($resource)->toArray();

        return $data['data'];

    }

    public function byUid(Process $daemon, $uid) {

        $data = self::getManager($daemon)->getOne(['uid' => $uid]);

        if ( empty($data) ) return null;

        $resource = new Item($data, new Transformer);
        $fractal = new FractalManager();
        $data = $fractal->createData($resource)->toArray();

        return $data['data'];

    }

    public function byPid(Process $daemon, $pid) {

        $data = self::getManager($daemon)->get(['pid' => $pid]);

        $resource = new Collection($data, new Transformer);
        $fractal = new FractalManager();
        $data = $fractal->createData($resource)->toArray();

        return $data['data'];

    }

    protected static function getManager(Process $daemon) {

        return new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

    }

}
