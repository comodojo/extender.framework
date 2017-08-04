<?php namespace Comodojo\Extender\Schedule;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Daemon\Traits\LoggerTrait;
use \Comodojo\Daemon\Traits\EventsTrait;
use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Extender\Orm\Entities\Schedule;
use \Doctrine\ORM\EntityManager;
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
    use EntityManagerTrait;

    // protected $locker;

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
        Configuration $configuration,
        LoggerInterface $logger,
        EventsManager $events,
        EntityManager $em = null
    ) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setEvents($events);

        $em = is_null($em) ? Database::init($configuration)->getEntityManager() : $em;
        $this->setEntityManager($em);

        // $lock_path = $configuration->get('lockfiles-path');
        // $this->locker = new Locker("$lock_path/schedule.lock");
        // $this->locker->lock(0);
    }

    public function getJobById($id) {

        $em = $this->getEntityManager();

        return $repo->find('Comodojo\Extender\Orm\Entities\Schedule', $id);

    }

    public function getJobs($ready = false) {

        $time = new DateTime();

        $em = $this->getEntityManager();

        $repo = $em->getRepository('Comodojo\Extender\Orm\Entities\Schedule');

        $standby = $repo->findAll();

        return $ready ? array_filter($standby, function($job) use ($time) {
            return $job->shouldRunJob($time);
        }) : $standby;

    }

    public function getNextCycleTimestamp() {

        $items = $this->getJobs();

        $date = new DateTime();
        $timestamps = [];

        foreach ($items as $schedule) {

            $timestamps[] = $schedule->getNextPlannedRun($date)->getTimestamp();

        }

        return empty($timestamps) ? 0 : min($timestamps);

    }

    public function updateSchedules(array $results) {

        $em = $this->getEntityManager();

        foreach ($results as $result) {

            $id = $result->getJid();

            $job = $this->getJobById($id);

            if ( $job === null ) continue;

            if ( $job->getFirstrun() === null ) $job->setFirstrun($result->start);

            $job->setLastRun($result->start);

            $em->persist($job);

        }

        $em->flush();

    }

    // public function remove(array $queue) {
    //
    //     $em = $this->getEntityManager();
    //
    //     foreach ($queue as $record) {
    //         $em->remove($record);
    //     }
    //
    //     $em->flush();
    //
    // }
    //
    // public function add($name, Request $request) {
    //
    //     $em = $this->getEntityManager();
    //
    //     $uid = $this->doAddRequest($name, $request, $em);
    //
    //     $em->flush();
    //
    //     return $uid;
    //
    // }
    //
    // public function addBulk(array $queue) {
    //
    //     $em = $this->getEntityManager();
    //
    //     $records = [];
    //
    //     foreach ($queue as $name => $request) {
    //         $records[] = $this->doAddRequest($name, $request, $em);
    //     }
    //
    //     $em->flush();
    //
    //     return $records;
    //
    // }
    //
    // protected function doAddRequest($name, Request $request, EntityManager $em) {
    //
    //     $record = new Queue();
    //     $record->setName($name)->setRequest($request);
    //
    //     $em->persist($record);
    //
    //     return $request->getUid();
    //
    // }

}
