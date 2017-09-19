<?php namespace Comodojo\Extender\Workers;

use \Comodojo\Daemon\Worker\AbstractWorker;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Queue\Manager as QueueManager;
use \Comodojo\Extender\Task\Locker;
use \Comodojo\Extender\Task\Manager as TaskManager;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Extender\Traits\WorkerTrait;
use \Comodojo\Extender\Events\QueueEvent;

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

class QueueWorker extends AbstractWorker {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;
    use TasksTableTrait;
    use WorkerTrait;

    protected $locker;

    public function spinup() {

        $configuration = $this->getConfiguration();

        $base_path = $configuration->get('base-path');
        $lock_path = $configuration->get('run-path');
        $lock_file = "$base_path/$lock_path/queue.worker.lock";

        $this->locker = new Locker($lock_file);
        $this->locker->lock([]);

    }

    public function loop() {

        $configuration = $this->getConfiguration();
        $logger = $this->getLogger();
        $events = $this->getEvents();

        $queue_manager = new QueueManager(
            $configuration,
            $logger,
            $events
        );

        $queue = $queue_manager->get();

        if ( !empty($queue) ) {

            $events->emit( new QueueEvent('process', null, $queue) );

            $requests = $this->jobsToRequests($queue);

            $queue_manager->flush($queue);
            unset($queue_manager);

            $task_manager = new TaskManager(
                $this->locker,
                $configuration,
                $logger,
                $this->getTasksTable(),
                $events
            );

            $result = $task_manager->addBulk($requests)->run();
            unset($task_manager);

        } else {

            unset($queue_manager);

        }

        $this->locker->lock([]);

    }

    public function spindown() {

        $this->locker->release();

    }

}
