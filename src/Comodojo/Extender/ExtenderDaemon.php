<?php namespace Comodojo\Extender;

use \Comodojo\Extender\Workers\ScheduleWorker;
use \Comodojo\Extender\Workers\QueueWorker;
use \Comodojo\Extender\Task\Table as TasksTable;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Queue\Manager as QueueManager;
use \Comodojo\Extender\Schedule\Manager as ScheduleManager;
use \Comodojo\Extender\Orm\Entities\Schedule;
use \Comodojo\Extender\Traits\CacheTrait;
use \Comodojo\Daemon\Daemon as AbstractDaemon;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\Foundation\Utils\ArrayOps;
use \Comodojo\SimpleCache\Manager as SimpleCacheManager;
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

class ExtenderDaemon extends AbstractDaemon {

    use ConfigurationTrait;
    use EventsTrait;
    use LoggerTrait;
    use CacheTrait;
    use TasksTableTrait;

    protected static $default_properties = array(
        'pidfile' => '',
        'sockethandler' => '',
        'socketbuffer' => 1024,
        'sockettimeout' => 2,
        'socketmaxconnections' => 100,
        'niceness' => 0,
        'arguments' => '\\Comodojo\\Daemon\\Console\\DaemonArguments',
        'description' => 'Extender Daemon'
    );

    public function __construct(
        array $configuration,
        array $tasks,
        EventsManager $events = null,
        SimpleCacheManager $cache = null,
        LoggerInterface $logger = null
    ) {

        $this->configuration = new Configuration(self::$default_properties);
        $this->configuration->merge($configuration);

        $run_path = $this->getRunPath();

        if ( empty($this->configuration->get('sockethandler')) ) {
            $this->configuration->set('sockethandler', "unix://$run_path/extender.sock");
        }

        if ( empty($this->configuration->get('pidfile')) ) {
            $this->configuration->set('pidfile', "$run_path/extender.pid");
        }

        $logger = is_null($logger) ? LogManager::createFromConfiguration($this->configuration)->getLogger() : $logger;
        $events = is_null($events) ? EventsManager::create($logger) : $events;

        parent::__construct(ArrayOps::replaceStrict(self::$default_properties, $this->configuration->get()), $logger, $events);

        $table = new TasksTable($this->configuration, $this->getLogger(), $this->getEvents());
        $table->addBulk($tasks);
        $this->setTasksTable($table);

        $this->setCache(is_null($cache) ? SimpleCacheManager::createFromConfiguration($this->configuration, $this->logger) : $cache);

    }

    public function setup() {

        // try {
        //     if ( Database::validate($this->configuration) === false ){
        //         printf("\nWARNING: %s\n\n", "database seems to be not in sync!");
        //     }
        // } catch (Exception $e) {
        //     printf("\nFATAL ERROR: %s\n\n", $e->getMessage());
        //     $this->end(1);
        // }

        $this->installWorkers();

        $this->pushQueueCommands();
        $this->pushScheduleCommands();

    }

    protected function installWorkers() {

        // add workers
        $manager = $this->getWorkers();

        $schedule_worker = new ScheduleWorker("scheduler");
        $schedule_worker
            ->setConfiguration($this->getConfiguration())
            ->setLogger($this->getLogger())
            ->setEvents($this->getEvents())
            ->setTasksTable($this->getTasksTable());

        $queue_worker = new QueueWorker("queue");
        $queue_worker
            ->setConfiguration($this->getConfiguration())
            ->setLogger($this->getLogger())
            ->setEvents($this->getEvents())
            ->setTasksTable($this->getTasksTable());

        $manager
            ->install($schedule_worker, 1, true)
            ->install($queue_worker, 1, true);

    }

    protected function pushQueueCommands() {

        $this->getSocket()->getCommands()
            ->add('queue:add', function(Request $request, $daemon) {
                $manager = new QueueManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                return $manager->add($request);
            })
            ->add('queue:addBulk', function(array $requests, $daemon) {

                $manager = new QueueManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                return $manager->addBulk($requests);

            });

    }

    protected function pushScheduleCommands() {

        $this->getSocket()->getCommands()
            ->add('scheduler:refresh', function($data, $daemon) {

                return $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

            })
            ->add('scheduler:add', function(Schedule $data, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                $id = $manager->add($data);

                $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

                return $id;

            })
            ->add('scheduler:get', function($id, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                return $manager->get($id);

            })
            ->add('scheduler:getByName', function($name, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                return $manager->getByName($name);

            })
            ->add('scheduler:edit', function(Schedule $data, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                $edit = $manager->edit($data);

                $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

                return $edit;

            })
            ->add('scheduler:remove', function($name, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                $remove = $manager->remove($name);

                $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

                return $remove;

            })
            ->add('scheduler:enable', function($name, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                $edit = $manager->enable($name);

                $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

                return $edit;

            })
            ->add('scheduler:disable', function($name, $daemon) {

                $manager = new ScheduleManager(
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getEvents()
                );

                $edit = $manager->disable($name);

                $this->getWorkers()->get("scheduler")->getOutputChannel()->send('refresh');

                return $edit;

            });

    }

    private function getRunPath() {
        return $this->configuration->get('base-path')."/".$this->configuration->get('run-path');
    }

}
