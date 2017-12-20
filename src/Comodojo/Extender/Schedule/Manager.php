<?php namespace Comodojo\Extender\Schedule;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Orm\Entities\Schedule;
use \Comodojo\Extender\Events\ScheduleEvent;
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

    /**
     * Class constructor
     *
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

    }

    public function get($id) {

        $em = $this->getEntityManager();

        $data = $em->find('Comodojo\Extender\Orm\Entities\Schedule', $id);

        return $data;

    }

    public function getByName($name) {

        $em = $this->getEntityManager();

        $data = $em->getRepository('Comodojo\Extender\Orm\Entities\Schedule')->findBy(["name" => $name]);

        if ( empty($data) ) return null;

        return $data[0];

    }

    public function getAll($ready = false) {

        $logger = $this->getLogger();

        $time = new DateTime();

        $em = $this->getEntityManager();

        $standby = $em->getRepository('Comodojo\Extender\Orm\Entities\Schedule')->findAll();

        return $ready ? array_filter($standby, function($job) use ($time, $logger) {

            $name = $job->getName();
            $id = $job->getId();
            $enabled = $job->getEnabled();
            $firstrun = $job->getFirstrun();
            $lastrun = $job->getLastrun();
            $expression = $job->getExpression();

            if ( $lastrun !== null ) {
                $nextrun = $expression->getNextRunDate($lastrun);
            } else {
                $nextrun = $firstrun;
            }

            $shouldrun = $nextrun <= $time;

            $logger->debug("Job $name (id $id) will ".($shouldrun ? "" : "NOT ")."be executed", [
                'ENABLED' => $enabled,
                'NEXTRUN' => $nextrun->format('r'),
                'SHOULDRUN' => $shouldrun
            ]);

            return $shouldrun;

        }) : $standby;

    }

    public function add(Schedule $schedule) {

        $time = new DateTime();

        $schedule->setFirstrun($schedule->getNextPlannedRun($time));

        $this->getEvents()->emit( new ScheduleEvent('add', $schedule) );

        $em = $this->getEntityManager();

        $em->persist($schedule);

        $em->flush();

        return $schedule->getId();

    }

    public function addBulk(array $schedules) {

        $time = new DateTime();
        $records = [];
        $em = $this->getEntityManager();

        foreach ($schedules as $key => $schedule) {

            try {

                $schedule->setFirstrun($schedule->getNextPlannedRun($time));

                $this->getEvents()->emit( new ScheduleEvent('add', $schedule) );

                $em->persist($schedule);

                $em->flush();

            } catch (Exception $e) {

                $records[$key] = false;

                continue;

            }

            $records[$key] = $schedule->getId();

        }

        return $records;

    }

    public function edit(Schedule $schedule) {

        $em = $this->getEntityManager();

        $id = $schedule->getId();

        if ( empty($id) ) throw new Exception("Cannot edit scheule without id");

        $old_schedule = $this->get($schedule->getId());

        if ( empty($old_schedule) ) throw new Exception("Cannot find schedule with id $id");

        $this->getEvents()->emit( new ScheduleEvent('edit', $schedule, $old_schedule) );

        $old_schedule->merge($schedule);

        $em->persist($old_schedule);

        $em->flush();

        return true;

    }

    public function remove($id) {

        $em = $this->getEntityManager();

        $schedule = $this->get($id);

        if ( is_null($schedule) ) throw new Exception("Cannot find scheule $id");

        $this->getEvents()->emit( new ScheduleEvent('remove', $schedule) );

        $em->remove($schedule);

        $em->flush();

        return true;

    }

    public function removeByName($name) {

        $em = $this->getEntityManager();

        $schedule = $this->getByName($name);

        if ( is_null($schedule) ) throw new Exception("Cannot find scheule $name");

        $this->getEvents()->emit( new ScheduleEvent('remove', $schedule) );

        $em->remove($schedule);

        $em->flush();

        return true;

    }

    public function enable($id) {

        $em = $this->getEntityManager();

        $schedule = $this->get($id);

        if ( is_null($schedule) ) throw new Exception("Cannot find scheule $id");

        $this->getEvents()->emit( new ScheduleEvent('enable', $schedule) );

        $schedule->setEnabled(true);

        $em->persist($schedule);

        $em->flush();

        return true;

    }

    public function enableByName($name) {

        $em = $this->getEntityManager();

        $schedule = $this->getByName($name);

        if ( is_null($schedule) ) throw new Exception("Cannot find scheule $name");

        $this->getEvents()->emit( new ScheduleEvent('enable', $schedule) );

        $schedule->setEnabled(true);

        $em->persist($schedule);

        $em->flush();

        return true;

    }

    public function disable($id) {

        $em = $this->getEntityManager();

        $schedule = $this->get($id);

        if ( is_null($schedule) ) throw new Exception("Cannot find scheule $id");

        $this->getEvents()->emit( new ScheduleEvent('disable', $schedule) );

        $schedule->setEnabled(false);

        $em->persist($schedule);

        $em->flush();

        return true;

    }

    public function disableByName($name) {

        $em = $this->getEntityManager();

        $schedule = $this->getByName($name);

        if ( is_null($schedule) ) throw new Exception("Cannot find scheule $name");

        $this->getEvents()->emit( new ScheduleEvent('disable', $schedule) );

        $schedule->setEnabled(false);

        $em->persist($schedule);

        $em->flush();

        return true;

    }

}
