<?php namespace Comodojo\Extender\Traits;

use \Comodojo\Extender\Task\TaskParameters;

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

trait BaseEntityTrait {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="task", type="string", length=256, nullable=false)
     */
    protected $task;

    /**
     * @var string
     *
     * @ORM\Column(name="parameters", type="array", nullable=false)
     */
    protected $parameters = [];

    /**
     * Get queue item's id
     *
     * @return integer
     */
    public function getId() {

        return $this->id;

    }

    /**
     * Set queue item's id
     *
     * @param string $id
     * @return Schedule
     */
    public function setId($id) {

        $this->id = $id;

        return $this;

    }

    /**
     * Get queue item's name
     *
     * @return string
     */
    public function getName() {

        return $this->name;

    }

    /**
     * Set queue item's name
     *
     * @param string $name
     * @return Schedule
     */
    public function setName($name) {

        $this->name = $name;

        return $this;

    }

    /**
     * Get associated task
     *
     * @return string
     */
    public function getTask() {

        return $this->task;

    }

    /**
     * Set associated task
     *
     * @param string $task
     * @return Schedule
     */
    public function setTask($task) {

        $this->task = $task;

        return $this;

    }

    /**
     * Get queue item's parameters
     *
     * @return TaskParameters
     */
    public function getParameters() {

        return new TaskParameters($this->parameters);

    }

    /**
     * Set queue item's parameters
     *
     * @param TaskParameters $parameters
     * @return Schedule
     */
    public function setParameters(TaskParameters $parameters) {

        $this->parameters = $parameters->get();

        return $this;

    }

    /**
     * Returns the properties of this object as an array for ease of use
     *
     * @return array
     */
    public function toArray() {

        return get_object_vars($this);

    }

}
