<?php namespace Comodojo\Extender\Task;

use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Psr\Log\LoggerInterface;

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


class TaskItem {

    use ConfigurationTrait;
    use EventsTrait;
    use LoggerTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $description;

    /**
     * TaskItem Constructor
     *
     * @param string $name
     * @param string $class
     * @param string $description
     */
    public function __construct(
        Configuration $configuration,
        EventsManager $events,
        LoggerInterface $logger,
        $name,
        $class,
        $description = null
    ) {

        $this->setConfiguration($configuration);
        $this->setEvents($events);
        $this->setLogger($logger);

        $this->name = $name;
        $this->class = $class;
        $this->description = $description;

    }

    /**
     *
     * @return string
     */
    public function getName() {

        return $this->name;

    }

    /**
     *
     * @param string $name
     * @return TaskItem
     */
    public function setName($name) {

        $this->name = $name;

        return $this;

    }

    /**
     *
     * @return string
     */
    public function getClass() {

        return $this->class;

    }

    /**
     *
     * @param string $class
     * @return TaskItem
     */
    public function setClass($class) {

        $this->class = $class;

        return $this;

    }

    /**
     *
     * @return string
     */
    public function getDescription() {

        return $this->description;

    }

    /**
     *
     * @param string $description
     * @return TaskItem
     */
    public function setDescription($description) {

        $this->description = $description;

        return $this;

    }

    public function getInstance($name, TaskParameters $parameters = null) {

        $task_class = $this->getClass();

        if ( $parameters === null ) $parameters = new TaskParameters();

        return new $task_class(
            $this->getConfiguration(),
            $this->getEvents(),
            $this->getLogger(),
            $name,
            $parameters === null ? new TaskParameters() : $parameters
        );

    }

    /**
     * Static constructor
     *
     * @param string $name
     * @param string $class
     * @param string $description
     * @return TaskItem
     */
    public static function create($name, $class, $description = null) {

        return new TaskItem($name, $class, $description);

    }

}
