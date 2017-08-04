<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\DataAccess\ArrayAccessTrait;
use \Comodojo\Foundation\DataAccess\IteratorTrait;
use \Comodojo\Foundation\DataAccess\CountableTrait;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Psr\Log\LoggerInterface;
use \Iterator;
use \ArrayAccess;
use \Countable;
use \Exception;

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


class Table implements Iterator, ArrayAccess, Countable {

    use ArrayAccessTrait;
    use IteratorTrait;
    use CountableTrait;
    use LoggerTrait;
    use ConfigurationTrait;
    use EventsTrait;

    /**
     * @var array
     */
    private $data = [];

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        EventsManager $events
    ) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setEvents($events);

    }

    /**
     * Get the task item
     *
     * @param string $name
     * @return TaskItem|null
     */
    public function get($name) {

        if ( array_key_exists($name, $this->data) ) {
            return $this->data[$name];
        }

        return null;

    }

    /**
     * Add a new task to table
     *
     * @param string $name
     * @param string $class
     * @param string $description
     * @return bool
     */
    public function add($name, $class, $description = null) {

        if ( array_key_exists($name, $this->data) ) {
            $this->logger->warning("Skipping duplicate task $name ($class)");
            return false;
        }

        if ( empty($name) || empty($class) || !class_exists($class) ) {
            $this->logger->warning("Skipping invalid task definition", array(
                "NAME"       => $name,
                "CLASS"      => $class,
                "DESCRIPTION"=> $description
            ));
            return false;
        }

        $this->data[$name] = new TaskItem(
            $this->getConfiguration(),
            $this->getEvents(),
            $this->getLogger(),
            $name,
            $class,
            $description
        );

        $this->logger->debug("Task $name ($class) in table");

        return true;

    }

    /**
     * Delete a task from table
     *
     * @param string $name
     * @return bool
     */
    public function delete($name) {

        if ( array_key_exists($name, $this->data) ) {
            unset($this->data[$name]);
            return true;
        }

        return false;

    }

    /**
     * Load a bulk task list into the table
     *
     * @param array $tasks
     * @return bool
     */
    public function addBulk(array $tasks) {

        $result = [];

        foreach($tasks as $task) {

            if ( empty($task['name']) || empty($task['class']) ) {

                $this->logger->warning("Skipping invalid task definition", array(
                    "NAME"       => $name,
                    "CLASS"      => $class,
                    "DESCRIPTION"=> $description
                ));
                $result[] = false;

            } else {

                $result[] = $this->add($task['name'], $task['class'], empty($task['description']) ? null : $task['description']);

            }

        }

        return $result;

    }

    public static function create(
        Configuration $configuration,
        LoggerInterface $logger,
        EventsManager $events
    ) {

        return new Table($configuration, $logger, $events);

    }

}
