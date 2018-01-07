<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Utils\UniqueId;
use \Comodojo\Extender\Traits\TasksRequestTrait;
use \Comodojo\Extender\Socket\Messages\Task\Request as TaskRequestMessage;
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

    use TasksRequestTrait;

    /**
     * @var int
     */
    protected $uid;

    /**
     * @var int
     */
    protected $parent_uid;

    /**
     * @var int
     */
    protected $jid;

    /**
     * @var int
     */
    protected $start_timestamp;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @var Request
     */
    protected $done;

    /**
     * @var Request
     */
    protected $fail;

    /**
     * @var Request
     */
    protected $pipe;

    /**
     * @var TaskParameters
     */
    protected $parameters = null;

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $task
     * @param TaskParameters $parameters
     */
    public function __construct($name, $task, TaskParameters $parameters = null) {

        $this->uid = UniqueId::generate();

        $this->setName($name);
        $this->setTask($task);
        $this->setParameters($parameters);

    }

    /**
     * Get current unique id
     *
     * @return int
     */
    public function getUid() {

        return $this->uid;

    }

    public function setUid($uid) {

        $this->uid = $uid;

        return $this;

    }

    /**
     * Get parent unique id
     *
     * @return int
     */
    public function getParentUid() {

        return $this->parent_uid;

    }

    public function setParentUid($uid) {

        $this->parent_uid = $uid;

        return $this;

    }

    /**
     * Get current job id
     *
     * @return int
     */
    public function getJid() {

        return $this->jid;

    }

    public function setJid($jid) {

        $this->jid = $jid;

        return $this;

    }

    /**
     * Get parameters
     *
     * @return TaskParameters
     */
    public function getParameters() {

        return $this->parameters;

    }

    public function setParameters(TaskParameters $parameters = null) {

        $this->parameters = is_null($parameters) ? new TaskParameters() : $parameters;

        return $this;

    }

    /**
     * Get start timestamp (microseconds)
     *
     * @return float
     */
    public function getStartTimestamp() {

        return $this->start_timestamp;

    }

    public function setStartTimestamp($time) {

        $this->start_timestamp = $time;

        return $this;

    }

    /**
     * Get pid
     *
     * @return int
     */
    public function getPid() {

        return $this->pid;

    }

    public function setPid($pid) {

        $this->pid = $pid;

        return $this;

    }

    public function hasOnDone() {

        return $this->done !== null;

    }

    public function getOnDone() {

        return $this->done;

    }

    public function onDone(Request $request = null) {

        $this->done = $request;

        return $this;

    }

    public function hasOnFail() {

        return $this->fail !== null;

    }

    public function getOnFail() {

        return $this->fail;

    }

    public function onFail(Request $request = null) {

        $this->fail = $request;

        return $this;

    }

    public function hasPipe() {

        return $this->pipe !== null;

    }

    public function getPipe() {

        return $this->pipe;

    }

    public function pipe(Request $request = null) {

        $this->pipe = $request;

        return $this;

    }

    public function isChain() {

        return ( $this->done !== null || $this->fail !== null || $this->pipe !== null );

    }

    public function convertToMessage() {

        $message = new TaskRequestMessage();

        $message->setName($this->getName());
        $message->setTask($this->getTask());
        $message->setNiceness($this->getNiceness());
        $message->setMaxtime($this->getMaxtime());
        $message->setParameters($this->getParameters()->export());

        if ( $this->done !== null ) $message->onDone( $this->getOnDone()->convertToMessage() );
        if ( $this->fail !== null ) $message->onFail( $this->getOnFail()->convertToMessage() );
        if ( $this->pipe !== null ) $message->pipe( $this->getPipe()->convertToMessage() );

        return $message;

    }

    public static function create($name, $task, TaskParameters $parameters = null) {

        return new Request($name, $task, $parameters);

    }

    public static function createFromMessage(TaskRequestMessage $message) {

        $name = $message->getName();
        $task = $message->getTask();
        $niceness = $message->getNiceness();
        $maxtime = $message->getMaxtime();
        $parameters = $message->getParameters();
        $done = $message->getOnDone();
        $fail = $message->getOnFail();
        $pipe = $message->getPipe();

        if ( empty($name) || empty($task) ) {
            throw new Exception("Invalid task name or task reference");
        }

        $request = Request::create(
            $name,
            $task,
            new TaskParameters($parameters)
        );

        $request
            ->setNiceness($niceness)
            ->setMaxTime($maxtime)
            ->onDone();

        $request->onDone(empty($done) ? null : Request::createFromMessage($done));
        $request->onFail(empty($fail) ? null : Request::createFromMessage($fail));
        $request->pipe(empty($pipe) ? null : Request::createFromMessage($pipe));

        return $request;

    }

}
