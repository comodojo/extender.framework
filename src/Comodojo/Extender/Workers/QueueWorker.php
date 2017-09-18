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

        $task_manager = new TaskManager(
            $this->locker,
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getTasksTable(),
            $this->getEvents()
        );

        $queue_manager = new QueueManager(
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getEvents()
        );

        $queue = $queue_manager->get();

        if ( !empty($queue) ) {

            $requests = $this->jobsToRequests($queue);

            $queue_manager->remove($queue);
            unset($queue_manager);

            $result = $task_manager->addBulk($requests)->run();
            unset($task_manager);

        } else {

            unset($queue_manager);
            unset($task_manager);

        }

        $this->locker->lock([]);

    }

    public function spindown() {

        $this->locker->release();

    }

}
