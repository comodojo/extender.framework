<?php namespace Comodojo\Extender\Worklog;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Orm\Entities\Worklog;
use \Comodojo\Extender\Events\WorklogEvent;
use \Doctrine\ORM\EntityManager;
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

    public function count() {

        $em = $this->getEntityManager();

        $query = $em->createQuery('SELECT COUNT(w.id) FROM Comodojo\Extender\Orm\Entities\Worklog w');

        return $query->getSingleScalarResult();

    }

    public function get(
        array $filter = [],
        $limit = 10,
        $offset = 0,
        $reverse = false
    ) {

        $em = $this->getEntityManager();

        return $em->getRepository('Comodojo\Extender\Orm\Entities\Worklog')
            ->findBy(
                $filter,
                ['id' => $reverse ? 'DESC' : 'ASC'],
                $limit,
                $offset
            );

    }

    public function getOne(array $filter) {

        $em = $this->getEntityManager();

        return $em->getRepository('Comodojo\Extender\Orm\Entities\Worklog')
            ->findOneBy($filter);
    }

}
