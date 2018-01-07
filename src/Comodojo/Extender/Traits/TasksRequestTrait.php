<?php namespace Comodojo\Extender\Traits;

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

trait TasksRequestTrait {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $task;

    /**
     * @var int
     */
    protected $niceness = 0;

    /**
     * @var int
     */
    protected $maxtime = 600;

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
    public function getTask() {

        return $this->task;

    }

    public function setTask($task) {

        $this->task = $task;

        return $this;

    }

    /**
     * Get requested niceness (-20/20)
     *
     * @return int
     */
    public function getNiceness() {

        return $this->niceness;

    }

    public function setNiceness($niceness) {

        $this->niceness = Validator::niceness($niceness);

        return $this;

    }

    /**
     * Get max allowed execution time
     *
     * @return int
     */
    public function getMaxtime() {

        return $this->maxtime;

    }

    public function setMaxtime($maxtime) {

        $this->maxtime = Validator::maxChildRuntime($maxtime);

        return $this;

    }

}
