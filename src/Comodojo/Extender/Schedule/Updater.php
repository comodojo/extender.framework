<?php namespace Comodojo\Extender\Schedule;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Foundation\Base\ConfigurationTrait;
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

class Updater {

    use ConfigurationTrait;
    use LoggerTrait;
    use EventsTrait;

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
        EventsManager $events
    ) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setEvents($events);

    }

    public function updateFromResults(array $results = []) {

        $em = Database::init($this->getConfiguration())->getEntityManager();

        foreach ($results as $result) {

            $id = $result->jid;

            if ( $id === null ) continue;

            $job = $em->find('Comodojo\Extender\Orm\Entities\Schedule', $id);

            if ( $job === null ) continue;

            if ( $job->getFirstrun() === null ) $job->setFirstrun($result->start);

            $job->setLastrun($result->start);

            $em->persist($job);

        }

        $em->flush();

        $ncts = $this->getNextCycleTimestamp($em);

        $em->close();

        return $ncts;

    }

    protected function getNextCycleTimestamp(EntityManager $em) {

        $items = $em->getRepository('Comodojo\Extender\Orm\Entities\Schedule')->findAll();

        $date = new DateTime();
        $timestamps = [];

        foreach ($items as $schedule) {

            $timestamps[] = $schedule->getNextPlannedRun($date)->getTimestamp();

        }

        return empty($timestamps) ? 0 : min($timestamps);

    }

}
