<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\TasksTableTrait;
use \Comodojo\Extender\Utils\Validator as ExtenderCommonValidations;
use \Comodojo\Extender\Components\Ipc;
use \Comodojo\Extender\Task\Table as TasksTable;
use \Comodojo\Daemon\Utils\ProcessTools;
use \Doctrine\ORM\EntityManager;
use \Comodojo\Extender\Traits\EntityManagerTrait;
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
    use EntityManagerTrait;

    protected $lagger_timeout;

    protected $multithread;

    protected $max_runtime;

    protected $max_childs;

    protected $ipc;

    protected $locker;

    protected $runner;

    public function __construct(
        $manager_name,
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
        $this->setEntityManager($em);

        $this->ipc = new Ipc($configuration);

        $this->locker = Locker::create($manager_name, $configuration, $logger);

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

    public function add(Request $request) {

        return $this->locker->setQueued($request);

    }

    public function addBulk(array $requests) {

        $responses = [];

        foreach ($requests as $id => $request) {

            if ($request instanceof \Comodojo\Extender\Task\Request) {
                $responses[$id] = $this->add($request);
            } else {
                $this->logger->error("Skipping invalid request with local id $id: class mismatch");
                $responses[$id] = false;
            }

        }

        return $responses;

    }

    public function run() {

        foreach ($this->locker->getQueued() as $uid => $request) {

            if ( $this->multithread === false ) {

                $pid = ProcessTools::getPid();

                $this->locker->setRunning($uid, $pid);

                $result = Runner::fastStart(
                    $request,
                    $this->getConfiguration(),
                    $this->getLogger(),
                    $this->getTasksTable(),
                    $this->getEvents(),
                    $this->getEntityManager()
                );

                $this->locker->setCompleted($uid, $result);

                continue;

            }

            try {

                $pid = $this->forker($request);

            } catch (Exception $e) {

                $this->locker->setAborted($uid, self::generateSyntheticResult($uid, $e->getMessage(), false));

                continue;

            }

            $this->locker->setRunning($uid, $pid);

            if ( $this->max_childs > 0 && $this->locker->countRunning() >= $this->max_childs ) {

                while( $this->locker->countRunning() >= $this->max_childs ) {

                    $this->catcher();

                }

            }

            // is tihs the right way to terminate loop?
            // if ( $this->active === false ) return;

        }

        if ( $this->multithread === true ) $this->catcher_loop();

        $return = $this->locker->getCompleted();

        $this->locker->freeCompleted();

        return $return;

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

            // $this->logger->error("Could not fok job, aborting");

            throw new Exception("Unable to fork job, aborting");

        } elseif ( $pid ) {

            //PARENT will take actions on processes later

            $niceness = $request->getNiceness();

            if ( $niceness !== null ) ProcessTools::setNiceness($niceness, $pid);

        } else {

            $this->ipc->close($uid, Ipc::READER);

            $result = Runner::fastStart(
                $request,
                $this->getConfiguration(),
                $this->getLogger(),
                $this->getTasksTable(),
                $this->getEvents(),
                $this->getEntityManager()
            );

            // $output = array(
            //     'success' => $result->success,
            //     'result' => $result->result,
            //     'wid' => $result->wid
            // );

            $this->ipc->write($uid, serialize($result));

            $this->ipc->close($uid, Ipc::WRITER);

            exit(!$result->success);

        }

        return $pid;

    }

    private function catcher_loop() {

        while ( !empty($this->locker->getRunning()) ) {

            $this->catcher();

        }

    }

    /**
     * Catch results from completed jobs
     *
     */
    private function catcher() {

        foreach ( $this->locker->getRunning() as $uid => $request ) {

            if ( ProcessTools::isRunning($request->getPid()) === false ) {

                $this->ipc->close($uid, Ipc::WRITER);

                try {

                    $raw_output = $this->ipc->read($uid);

                    $result = unserialize(rtrim($raw_output));

                    $this->ipc->close($uid, Ipc::READER);

                } catch (Exception $e) {

                    $result = self::generateSyntheticResult($uid, $e->getMessage(), false);

                    // $this->logger->error($result);

                }

                $this->locker->setCompleted($uid, $result);

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

                    $this->locker->setCompleted($uid, self::generateSyntheticResult($uid, "Job killed due to max runtime reached", false));

                    $this->logger->notice("Task ".$request->getName()."(uid: $uid) ends in error");

                }

            }

        }

    }

    public function free() {

        $this->ipc->free();
        $this->locker->free();

    }

    private function generateSyntheticResult($uid, $message, $success = true) {

        return new Result([
            $uid,
            null,
            null,
            $success,
            null,
            null,
            $message,
            null
        ]);

    }

}
