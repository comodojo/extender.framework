<?php namespace Comodojo\Extender\Task;

use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Task\Table as TasksTable;
use \Comodojo\Extender\Events\TaskEvent;
use \Comodojo\Extender\Events\TaskStatusEvent;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Orm\Entities\Worklog;
use \Comodojo\Extender\Utils\StopWatch;
use \Psr\Log\LoggerInterface;
use \Doctrine\ORM\EntityManager;
use \Comodojo\Exception\TaskException;
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

class Runner {

    use LoggerTrait;
    use ConfigurationTrait;
    use EventsTrait;
    use TasksTableTrait;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Worklog
     */
    protected $worklog;

    /**
     * @var StopWatch
     */
    protected $stopwatch;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $table,
        EventsManager $events,
        EntityManager $em = null
    ) {

        // init components
        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setEvents($events);
        $this->setTasksTable($table);

        // create StopWatch
        $this->stopwatch = new StopWatch();

        // init database
        $this->em = is_null($em) ? Database::init($configuration)->getEntityManager() : $em;

        // init worklog manager
        $this->worklog = new Worklog();

    }

    public function run(Request $request) {

        $name = $request->getName();
        $task = $request->getTask();
        $uid = $request->getUid();
        $jid = $request->getJid();
        $puid = $request->getParentUid();
        $parameters = $request->getParameters();

        try {

            $this->stopwatch->start();

            $this->logger->info("Starting new task $task ($name)");

            $thetask = $this->table->get($task)->getInstance($name, $parameters);

            $this->events->emit( new TaskEvent('start', $thetask) );

            $pid = $thetask->getPid();

            $this->openWorklog(
                $uid,
                $puid,
                $pid,
                $name,
                $jid,
                $task,
                $parameters,
                $this->stopwatch->getStartTime()
            );

            $this->events->emit( new TaskStatusEvent('start', $thetask) );

            try {

                $result = $thetask->run();

                $status = Worklog::STATUS_FINISHED;

            } catch (TaskException $te) {

                $status = Worklog::STATUS_ABORTED;

                $result = $te->getMessage();

            } catch (Exception $e) {

                $status = Worklog::STATUS_ERROR;

                $result = $e->getMessage();

            }

            $this->events->emit( new TaskStatusEvent($status ? 'success' : 'error', $thetask) );

            $this->events->emit( new TaskStatusEvent('stop', $thetask) );

            $this->events->emit( new TaskEvent('stop', $thetask) );

            $this->stopwatch->stop();

            $this->closeWorklog($status, $result, $this->stopwatch->getStopTime());

            $drift = $this->stopwatch->getDrift()->format('%s');

            $this->logger->notice("Task $name ($task) with pid $pid ends in ".($status ? 'success' : 'error')." in $drift secs");

        } catch (Exception $e) {

            throw $e;

        }

        $result = new Result([
            $uid,
            $pid,
            $name,
            $status,
            $this->stopwatch->getStartTime(),
            $this->stopwatch->getStopTime(),
            $result,
            $this->worklog->getId()
        ]);

        $this->stopwatch->clear();

        return $result;

    }

    public static function fastStart(
        Request $request,
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $table,
        EventsManager $events,
        EntityManager $em = null
    ) {

        $runner = new Runner(
            $configuration,
            $logger,
            $table,
            $events,
            $em
        );

        return $runner->run($request);

    }

    protected function openWorklog(
        $uid,
        $puid,
        $pid,
        $name,
        $jid,
        $task,
        $parameters,
        $start
    ) {

        $this->worklog
            ->setUid($uid)
            ->setParentUid($puid)
            ->setPid($pid)
            ->setName($name)
            ->setStatus(Worklog::STATUS_RUNNING)
            ->setJid($jid)
            ->setTask($task)
            ->setParameters($parameters)
            ->setStartTime($start);

        $this->em->persist($this->worklog);
        $this->em->flush();

    }

    protected function closeWorklog(
        $status,
        $result,
        $end
    ) {

        $this->worklog
            ->setStatus($status)
            ->setResult($result)
            ->setEndTime($end);

        $this->em->persist($this->worklog);
        $this->em->flush();

    }

}
