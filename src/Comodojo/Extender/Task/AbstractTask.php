<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Extender\Interfaces\TaskInterface;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Daemon\Traits\PidTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Daemon\Utils\ProcessTools;
use \Psr\Log\LoggerInterface;
use \Comodojo\Foundation\Events\Manager as EventsManager;

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

abstract class AbstractTask implements TaskInterface {

    use ConfigurationTrait;
    use EventsTrait;
    use LoggerTrait;
    use PidTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var TaskParameters
     */
    protected $parameters;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        Configuration $configuration,
        EventsManager $events,
        LoggerInterface $logger,
        $name,
        TaskParameters $parameters
    ) {

        // Setup task
        $this->setConfiguration($configuration);
        $this->setEvents($events);
        $this->setLogger($logger);
        $this->setName($name);
        $this->setParameters($parameters);
        $this->setPid(ProcessTools::getPid());

    }

    /**
     * Get niceness of a running process
     *
     * @param int|null $pid The pid to query, or current process if null
     * @return int
     */
    public function getName() {

        return $this->name;

    }

    /**
     * Get niceness of a running process
     *
     * @param int|null $pid The pid to query, or current process if null
     * @return int
     */
    public function getParameters() {

        return $this->parameters;

    }

    /**
     * Get niceness of a running process
     *
     * @param int|null $pid The pid to query, or current process if null
     * @return int
     */
    public function setName($name) {

        $this->name = $name;

        return $this;

    }

    /**
     * Get niceness of a running process
     *
     * @param int|null $pid The pid to query, or current process if null
     * @return int
     */
    public function setParameters(TaskParameters $parameters) {

        $this->parameters = $parameters;

        return $this;

    }

    /**
     * The run method; SHOULD be implemented by each task
     */
    abstract public function run();

}
