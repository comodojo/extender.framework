<?php namespace Comodojo\Extender\Socket\Messages\Task;

use \Comodojo\Extender\Traits\TasksRequestTrait;
use \Serializable;

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

class Request implements Serializable {

    use TasksRequestTrait;

    protected $parameters = [];

    protected $done;

    protected $fail;

    protected $pipe;

    // public function __construct($name, $task, array $parameters = null) {
    //
    //     $this->setName($name);
    //     $this->setTask($task);
    //     $this->setParameters($parameters);
    //
    // }

    public function setParameters(array $parameters = null) {

        $this->parameters = empty($parameters) ? [] : $parameters;

        return $this;

    }

    public function getParameters() {

        return $this->parameters;

    }

    public function getOnDone() {

        return $this->done;

    }

    public function onDone(Request $request = null) {

        $this->done = $request;

        return $this;

    }

    public function getOnFail() {

        return $this->fail;

    }

    public function onFail(Request $request = null) {

        $this->fail = $request;

        return $this;

    }

    public function getPipe() {

        return $this->pipe;

    }

    public function pipe(Request $request = null) {

        $this->pipe = $request;

        return $this;

    }

    public function import(array $data) {

        $this->setName($data['name']);
        $this->setTask($data['task']);
        $this->setNiceness($data['niceness']);
        $this->setMaxtime($data['maxtime']);
        $this->setParameters($data['parameters']);

        $this->onDone(empty($data['done']) ? null : Request::createFromExport($data['done']));
        $this->onFail(empty($data['fail']) ? null : Request::createFromExport($data['fail']));
        $this->pipe(empty($data['pipe']) ? null : Request::createFromExport($data['pipe']));

    }

    public function export() {

        return [
            'name' => $this->getName(),
            'task' => $this->getTask(),
            'niceness' => $this->getNiceness(),
            'maxtime' => $this->getMaxtime(),
            'parameters' => $this->getParameters(),
            'done' => empty($this->done) ? null : $this->done->export(),
            'fail' => empty($this->fail) ? null : $this->fail->export(),
            'pipe' => empty($this->pipe) ? null : $this->pipe->export()
        ];

    }

    public function serialize() {

        return serialize($this->export());

    }

    public function unserialize($data) {

        $data = unserialize($data);

        $this->import($data);

    }

    public static function create($name, $task, array $parameters = null) {

        $request = new Request();

        return $request->setName($name)
            ->setTask($task)
            ->setParameters($parameters);

    }

    public static function createFromExport(array $export) {

        $r = new Request();
        $r->import($export);

        return $r;

    }

}
