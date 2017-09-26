<?php namespace Comodojo\Extender\Schedule;

use \Comodojo\Daemon\Process;
use \Comodojo\Extender\Orm\Entities\Schedule;

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

    public function schedulerRefresh(Process $daemon, $data = null) {

        return $daemon->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

    }

    public function schedulerAdd(Process $daemon, Schedule $schedule) {

        $id = self::getManager($daemon)->add($schedule);

        $this->schedulerRefresh($daemon);

        return $id;

    }

    public function schedulerGet(Process $daemon, $id) {

        return self::getManager($daemon)->get($id);

    }

    public function schedulerGetByName(Process $daemon, $name) {

        return self::getManager($daemon)->getByName($name);

    }

    public function schedulerEdit(Process $daemon, Schedule $schedule) {

        $edit = self::getManager($daemon)->edit($schedule);

        $this->schedulerRefresh($daemon);

        return $edit;

    }

    public function schedulerRemove(Process $daemon, $id) {

        $remove = self::getManager($daemon)->remove($id);

        $this->schedulerRefresh($daemon);

        return $remove;

    }

    public function schedulerRemoveByName(Process $daemon, $name) {

        $remove = self::getManager($daemon)->removeByName($name);

        $this->schedulerRefresh($daemon);

        return $remove;

    }

    public function schedulerEnable(Process $daemon, $id) {

        $enable = self::getManager($daemon)->enable($id);

        $this->schedulerRefresh($daemon);

        return $enable;

    }

    public function schedulerEnableByName(Process $daemon, $name) {

        $enable = self::getManager($daemon)->enableByName($name);

        $this->schedulerRefresh($daemon);

        return $enable;

    }

    public function schedulerDisable(Process $daemon, $id) {

        $disable = self::getManager($daemon)->disable($id);

        $this->schedulerRefresh($daemon);

        return $disable;

    }

    public function schedulerDisableByName(Process $daemon, $name) {

        $disable = self::getManager($daemon)->disableByName($name);

        $this->schedulerRefresh($daemon);

        return $disable;

    }

    public function schedulerInfo(Process $daemon, $data = null) {

        $configuration = $daemon->getConfiguration();
        $base_path = $configuration->get('base-path');
        $lock_path = $configuration->get('run-path');
        $lock_file = "$base_path/$lock_path/schedule.worker.lock";

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
