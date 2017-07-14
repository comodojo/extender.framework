<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Utils\Validator as ExtenderCommonValidations;
use \Psr\Log\LoggerInterface;
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


class Manager {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;
    use TasksTableTrait;

    protected $lagger_timeout;

    protected $multithread;

    protected $max_runtime;

    protected $max_childs;

    protected $ipc;

    protected $status_table;

    protected $runner;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $tasks,
        EventsManager $events,
        EntityManager $em = null
    ) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setTasksTable($tasks);
        $this->setEvents($events);

        $em = is_null($em) ? Database::init($configuration)->getEntityManager() : $em;

        $this->ipc = new Ipc($configuration);
        $this->locker = Locker::create($configuration, $logger);

        // init the runner
        $this->runner = new Runner(
            $configuration,
            $logger,
            $tasks,
            $events,
            $em
        );

        // retrieve parameters
        $this->lagger_timeout = ExtenderCommonValidations::laggerTimeout($this->configuration->get('child-lagger-timeout'));
        $this->multithread = ExtenderCommonValidations::multithread($this->configuration->get('multithread'));
        $this->max_runtime = ExtenderCommonValidations::maxChildRuntime($this->configuration->get('child-max-runtime'));
        $this->max_childs = ExtenderCommonValidations::forkLimit($this->configuration->get('fork-limit'));

        $logger->debug("Tasks Manager online", array(
            'lagger_timeout' => $this->lagger_timeout,
            'multithread' => $this->multithread,
            'max_runtime' => $this->max_runtime,
            'max_childs' => $this->max_childs
        ));

    }

    public function __destruct() {

        $this->locker->release();

    }

}
