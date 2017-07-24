<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Utils\UniqueId;
use \Comodojo\Extender\Utils\Validator;

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


class Request {

    protected $name;

    protected $task;

    protected $uid;

    protected $jid = null;

    protected $niceness = 0;

    protected $maxtime = 600;

    protected $start_timestamp;

    protected $pid;

    /**
     * @var TaskParameters
     */
    protected $parameters = null;

    public function __construct($name, $task, TaskParameters $parameters = null) {

        $this->uid = UniqueId::generate();

        $this->setName($name);
        $this->setTask($task);
        $this->setParameters($parameters);

    }

    public function getName() {

        return $this->name;

    }

    protected function setName($name) {

        $this->name = $name;

        return $this;

    }

    public function getTask() {

        return $this->task;

    }

    protected function setTask($task) {

        $this->task = $task;

        return $this;

    }

    public function getUid() {

        return $this->uid;

    }

    protected function setUid($uid) {

        $this->uid = $uid;

        return $this;

    }

    public function getJid() {

        return $this->jid;

    }

    protected function setJid($jid) {

        $this->jid = $jid;

        return $this;

    }

    public function getNiceness() {

        return $this->niceness;

    }

    protected function setNiceness($niceness) {

        $this->niceness = Validator::niceness($niceness);

        return $this;

    }

    public function getMaxtime() {

        return $this->maxtime;

    }

    protected function setMaxtime($maxtime) {

        $this->maxtime = Validator::maxChildRuntime($maxtime);

        return $this;

    }

    public function getParameters() {

        return $this->parameters;

    }

    protected function setParameters(TaskParameters $parameters = null) {

        $this->parameters = is_null($parameters) ? new TaskParameters() : $parameters;

        return $this;

    }

    public function getStartTimestamp() {

        return $this->start_timestamp;

    }

    public function setStartTimestamp($time) {

        $this->start_timestamp = $time;

        return $this;

    }

    public function getPid() {

        return $this->pid;

    }

    public function setPid($pid) {

        $this->pid = $pid;

        return $this;

    }

}
