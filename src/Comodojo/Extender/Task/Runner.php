<?php namespace Comodojo\Extender\Task;

use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Task\Table as TasksTable;
use \Comodojo\Extender\Events\TaskEvent;
use \Comodojo\Extender\Events\WorklogEvent;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Traits\TaskErrorHandlerTrait;
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
    use TaskErrorHandlerTrait;

    /**
     * @var int
     */
    protected $worklog_id;

    /**
     * @var StopWatch
     */
    protected $stopwatch;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $table,
        EventsManager $events
    ) {

        // init components
        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setEvents($events);
        $this->setTasksTable($table);

        // create StopWatch
        $this->stopwatch = new StopWatch();

    }

    public function run(Request $request) {

        $name = $request->getName();
        $task = $request->getTask();
        $uid = $request->getUid();
        $jid = $request->getJid();
        $puid = $request->getParentUid();
        $parameters = $request->getParameters();

        ob_start();

        try {

            $this->stopwatch->start();

            $this->logger->notice("Starting new task $task ($name)");

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

            $this->installErrorHandler();

            try {

                $result = $thetask->run();

                $status = Worklog::STATUS_COMPLETE;

                $this->events->emit( new TaskEvent('complete', $thetask) );

            } catch (TaskException $te) {

                $status = Worklog::STATUS_ABORT;

                $result = $te->getMessage();

                $this->events->emit( new TaskEvent('abort', $thetask) );

            } catch (Exception $e) {

                $status = Worklog::STATUS_ERROR;

                $result = $e->getMessage();

                $this->events->emit( new TaskEvent('error', $thetask) );

            }

            $this->restoreErrorHandler();

            $this->events->emit( new TaskEvent('stop', $thetask) );

            $this->stopwatch->stop();

            $this->closeWorklog($status, $result, $this->stopwatch->getStopTime());

            $drift = $this->stopwatch->getDrift()->format('%s');

            $this->logger->notice("Task $name ($task) pid $pid ends in ".($status === Worklog::STATUS_COMPLETE ? 'success' : 'error')." in $drift secs");

        } catch (Exception $e) {

            ob_end_clean();

            throw $e;

        }

        $result = new Result([
            $uid,
            $pid,
            $jid,
            $name,
            $status === Worklog::STATUS_COMPLETE ? true : false,
            $this->stopwatch->getStartTime(),
            $this->stopwatch->getStopTime(),
            $result,
            $this->worklog_id
        ]);

        $this->stopwatch->clear();

        ob_end_clean();

        $this->events->emit( new TaskEvent(self::statusToEvent($status), $thetask, $result) );

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

        try {

            $em = Database::init($this->getConfiguration())->getEntityManager();

            $worklog = new Worklog();

            $worklog
                ->setUid($uid)
                ->setParentUid($puid)
                ->setPid($pid)
                ->setName($name)
                ->setStatus(Worklog::STATUS_RUN)
                ->setTask($task)
                ->setParameters($parameters)
                ->setStartTime($start);

            if ( $jid !== null ) {
                $schedule = $em->find('\Comodojo\Extender\Orm\Entities\Schedule', $jid);
                $worklog->setJid($schedule);
            }

            $this->events->emit( new WorklogEvent('open', $worklog) );

            $em->persist($worklog);
            $em->flush();

            $this->worklog_id = $worklog->getId();

            //$em->getConnection()->close();
            $em->close();

        } catch (Exception $e) {
            throw $e;
        }

    }

    protected function closeWorklog(
        $status,
        $result,
        $end
    ) {

        try {

            $em = Database::init($this->getConfiguration())->getEntityManager();

            $worklog = $em->find('\Comodojo\Extender\Orm\Entities\Worklog', $this->worklog_id);

            $worklog
                ->setStatus($status)
                ->setResult($result)
                ->setEndTime($end);

            $jid = $worklog->getJid();
            if ( $jid !== null ) {
                $schedule = $em->find('\Comodojo\Extender\Orm\Entities\Schedule', $jid);
                $worklog->setJid($schedule);
            }

            $this->events->emit( new WorklogEvent('close', $worklog) );

            $em->persist($worklog);
            $em->flush();
            //$em->getConnection()->close();
            $em->close();

        } catch (Exception $e) {
            throw $e;
        }

    }

    protected function statusToEvent($status) {

        switch ($status) {
            case Worklog::STATUS_COMPLETE:
                return 'complete';
                break;
            case Worklog::STATUS_ABORT:
                return 'abort';
                break;
            case Worklog::STATUS_ERROR:
                return 'error';
                break;
        }

    }

}
