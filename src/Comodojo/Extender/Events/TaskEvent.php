<?php namespace Comodojo\Extender\Events;

use \Comodojo\Foundation\Events\AbstractEvent;
use \Comodojo\Extender\Interfaces\TaskInterface;
use \Comodojo\Extender\Task\Result;

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

class TaskEvent extends AbstractEvent {

    private $task;

    private $result;

    public function __construct($event, TaskInterface $task, Result $result = null) {

        parent::__construct("extender.task.$event");

        $this->task = $task;
        $this->result = $result;

    }

    public function getTask() {

        return $this->task;

    }

    public function getResult() {

        return $this->result;

    }

}
