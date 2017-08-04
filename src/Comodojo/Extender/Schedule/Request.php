<?php namespace Comodojo\Extender\Schedule;

use \Comodojo\Extender\Task\Request as TaskRequest;
use \Cron\CronExpression;

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

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var TaskRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $expression;

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $task
     * @param TaskParameters $parameters
     */
    public function __construct($name, $expression, TaskRequest $request, $description = null) {

        $this->setName($name)
            ->setExpression($expression)
            ->setRequest($request)
            ->setDescription($description);

    }

    /**
     * Get request name
     *
     * @return string
     */
    public function getName() {

        return $this->name;

    }

    public function setName($name) {

        $this->name = $name;

        return $this;

    }

    /**
     * Get request associated task
     *
     * @return string
     */
    public function getTaskRequest() {

        return $this->request;

    }

    public function setTaskRequest(TaskRequest $request) {

        $this->request = $request;

        return $this;

    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {

        return $this->description;

    }

    public function setDescription($description = null) {

        $this->description = $description;

        return $this;

    }

    /**
     * Get parent unique id
     *
     * @return int
     */
    public function getExpression() {

        return $this->expression;

    }

    public function setCronExpression(CronExpression $expression) {

        $this->expression = $expression;

        return $this;

    }

    public static function create($name, $task, TaskParameters $parameters = null) {

        return new Request($name, $task, $parameters);

    }

}
