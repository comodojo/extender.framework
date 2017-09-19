<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Daemon\Utils\ProcessTools;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Utils\Validator as ExtenderCommonValidations;
use \Comodojo\Extender\Components\Ipc;
use \Comodojo\Extender\Task\Table as TasksTable;
use \Comodojo\Extender\Components\Database;
use \Psr\Log\LoggerInterface;
use \DateTime;
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

    /**
     * @var int
     */
    protected $lagger_timeout;

    /**
     * @var bool
     */
    protected $multithread;

    /**
     * @var int
     */
    protected $max_runtime;

    /**
     * @var int
     */
    protected $max_childs;

    /**
     * @var Ipc
     */
    protected $ipc;

    /**
     * @var Locker
     */
    protected $locker;

    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * Class constructor
     *
     * @param string $manager_name
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     * @param TasksTable $tasks
     * @param EventsManager $events
     * @param EntityManager $em
     */
    public function __construct(
        Locker $locker,
        Configuration $configuration,
        LoggerInterface $logger,
        TasksTable $tasks,
        EventsManager $events
    ) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setTasksTable($tasks);
        $this->setEvents($events);

        $this->locker = $locker;
        $this->tracker = new Tracker($configuration, $logger);
        $this->ipc = new Ipc($configuration);

        // retrieve parameters
        $this->lagger_timeout = ExtenderCommonValidations::laggerTimeout($this->configuration->get('child-lagger-timeout'));
        $this->multithread = ExtenderCommonValidations::multithread($this->configuration->get('multithread'));
        $this->max_runtime = ExtenderCommonValidations::maxChildRuntime($this->configuration->get('child-max-runtime'));
        $this->max_childs = ExtenderCommonValidations::forkLimit($this->configuration->get('fork-limit'));

        // $logger->debug("Tasks Manager online", array(
        //     'lagger_timeout' => $this->lagger_timeout,
        //     'multithread' => $this->multithread,
        //     'max_runtime' => $this->max_runtime,
        //     'max_childs' => $this->max_childs,
        //     'tasks_count' => count($this->table)
        // ));

        set_error_handler([$this, 'customErrorHandler']);

    }

    public function __destruct() {

        restore_error_handler();

    }

    public function add(Request $request) {

        $this->tracker->setQueued($request);

        return $this;

    }

    public function addBulk(array $requests) {

        foreach ($requests as $id => $request) {

            if ($request instanceof \Comodojo\Extender\Task\Request) {
                $this->add($request);
            } else {
                $this->logger->error("Skipping invalid request with local id $id: class mismatch");
            }

        }

        return $this;

    }

    public function run() {

        $this->updateTrackerSetQueued();

        while ( $this->tracker->countQueued() > 0 ) {

            // Start to cycle queued tasks
            $this->cycle();

        }

        $this->ipc->free();

        return $this->tracker->getCompleted();

    }

    public function customErrorHandler($errno, $errstr, $errfile, $errline) {

        $this->getLogger()->error("Unhandled error ($errno): $errstr [in $errfile line $errline]");

        return true;

    }

    protected function cycle() {

        foreach ($this->tracker->getQueued() as $uid => $request) {

            if ( $this->multithread === false ) {

                $this->runSingleThread($uid, $request);

            } else {

                try {

                    $pid = $this->forker($request);

                } catch (Exception $e) {

                    $result = self::generateSyntheticResult($uid, $e->getMessage(), $request->getJid(), false);

                    $this->updateTrackerSetAborted($uid, $result);

                    if ( $request->isChain() ) $this->evalChain($request, $result);

                    continue;

                }

                $this->updateTrackerSetRunning($uid, $pid);

                if ( $this->max_childs > 0 && $this->tracker->countRunning() >= $this->max_childs ) {

                    while( $this->tracker->countRunning() >= $this->max_childs ) {

                        $this->catcher();

                    }

                }

            }

        }

        // spawn the loop if multithread
        if ( $this->multithread === true ) $this->catcher_loop();

    }

    protected function runSingleThread($uid, Request $request) {

        $pid = ProcessTools::getPid();

        $this->updateTrackerSetRunning($uid, $pid);

        $result = Runner::fastStart(
            $request,
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getTasksTable(),
            $this->getEvents()
        );

        if ( $request->isChain() ) $this->evalChain($request, $result);

        $this->updateTrackerSetCompleted($uid, $result);

        $success = $result->success === false ? "error" : "success";
        $this->logger->notice("Task ".$request->getName()."(uid: ".$request->getUid().") ends in $success");

    }

    private function forker(Request $request) {

        $uid = $request->getUid();

        try {

            $this->ipc->init($uid);

        } catch (Exception $e) {

            $this->logger->error("Aborting task ".$request->getName().": ".$e->getMessage());

            $this->ipc->hang($uid);

            throw $e;

        }

        $pid = pcntl_fork();

        if ( $pid == -1 ) {

            throw new Exception("Unable to fork job, aborting");

        } elseif ( $pid ) {

            $niceness = $request->getNiceness();

            if ( $niceness !== null ) ProcessTools::setNiceness($niceness, $pid);

        } else {

            $this->ipc->close($uid, Ipc::READER);

            $result = Runner::fastStart(
                $request,
                $this->getConfiguration(),
                $this->getLogger(),
                $this->getTasksTable(),
                $this->getEvents()
            );

            $this->ipc->write($uid, serialize($result));

            $this->ipc->close($uid, Ipc::WRITER);

            exit(!$result->success);

        }

        return $pid;

    }

    private function catcher_loop() {

        while ( !empty($this->tracker->getRunning()) ) {

            $this->catcher();

        }

    }

    /**
     * Catch results from completed jobs
     *
     */
    private function catcher() {

        foreach ( $this->tracker->getRunning() as $uid => $request ) {

            if ( ProcessTools::isRunning($request->getPid()) === false ) {

                $this->ipc->close($uid, Ipc::WRITER);

                try {

                    $raw_output = $this->ipc->read($uid);

                    $result = unserialize(rtrim($raw_output));

                    $this->ipc->close($uid, Ipc::READER);

                } catch (Exception $e) {

                    $result = self::generateSyntheticResult($uid, $e->getMessage(), $request->getJid(), false);

                }

                if ( $request->isChain() ) $this->evalChain($request, $result);

                $this->updateTrackerSetCompleted($uid, $result);

                $success = $result->success === false ? "error" : "success";
                $this->logger->notice("Task ".$request->getName()."(uid: ".$request->getUid().") ends in $success");

            } else {

                $current_time = microtime(true);

                $request_max_time = $request->getMaxtime();
                $maxtime = $request_max_time === null ? $this->max_runtime : $request_max_time;

                if ( $current_time > $request->getStartTimestamp() + $maxtime ) {

                    $pid = $request->getPid();

                    $this->logger->warning("Killing pid $pid due to maximum exec time reached", [
                        "START_TIME"    => $request->getStartTimestamp(),
                        "CURRENT_TIME"  => $current_time,
                        "MAX_RUNTIME"   => $maxtime
                    ]);

                    $kill = ProcessTools::term($pid, $this->lagger_timeout);

                    if ( $kill ) {
                        $this->logger->warning("Pid $pid killed");
                    } else {
                        $this->logger->warning("Pid $pid could not be killed");
                    }

                    $this->ipc->hang($uid);

                    $result = self::generateSyntheticResult($uid, "Job killed due to max runtime reached", $request->getJid(), false);

                    if ( $request->isChain() ) $this->evalChain($request, $result);

                    $this->updateTrackerSetCompleted($uid, $result);

                    $this->logger->notice("Task ".$request->getName()."(uid: $uid) ends in error");

                }

            }

        }

    }

    private function evalChain(Request $request, Result $result) {

        if ( $result->success && $request->hasOnDone() ) {
            $chain_done = $request->getOnDone();
            $chain_done->getParameters()->set('parent', $result);
            $chain_done->setParentUid($result->uid);
            $this->add($chain_done);
        }

        if ( $result->success === false && $request->hasOnFail() ) {
            $chain_fail = $request->getOnFail();
            $chain_fail->getParameters()->set('parent', $result);
            $chain_fail->setParentUid($result->uid);
            $this->add($chain_fail);
        }

        if ( $request->hasPipe() ) {
            $chain_pipe = $request->getPipe();
            $chain_pipe->getParameters()->set('parent', $result);
            $chain_pipe->setParentUid($result->uid);
            $this->add($chain_pipe);
        }

    }

    private function generateSyntheticResult($uid, $message, $jid = null, $success = true) {

        return new Result([
            $uid,
            null,
            $jid,
            null,
            $success,
            new DateTime(),
            null,
            $message,
            null
        ]);

    }

    private function updateTrackerSetQueued() {

        $this->locker->lock([
            'QUEUED' => $this->tracker->countQueued()
        ]);

    }

    private function updateTrackerSetRunning($uid, $pid) {

        $this->tracker->setRunning($uid, $pid);
        $this->locker->lock([
            'QUEUED' => $this->tracker->countQueued(),
            'RUNNING' => $this->tracker->countRunning()
        ]);

    }

    private function updateTrackerSetCompleted($uid, $result) {

        $this->tracker->setCompleted($uid, $result);

        $lock_data = [
            'RUNNING' => $this->tracker->countRunning(),
            'COMPLETED' => 1
        ];

        if ( $result->success ) {
            $lock_data['SUCCEEDED'] = 1;
        } else {
            $lock_data['FAILED'] = 1;
        }

        $this->locker->lock($lock_data);

    }

    private function updateTrackerSetAborted($uid, $result) {

        $this->tracker->setAborted($uid, $result);
        $lock_data = [
            'RUNNING' => $this->tracker->countRunning(),
            'ABORTED' => 1
        ];

    }

}
