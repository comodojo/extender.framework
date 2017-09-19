<?php namespace Comodojo\Extender\Queue;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Extender\Components\Database;
use \Comodojo\Extender\Traits\EntityManagerTrait;
use \Comodojo\Extender\Task\Request;
use \Comodojo\Extender\Orm\Entities\Queue;
use \Comodojo\Extender\Events\QueueEvent;
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

        // $logger->debug("Tasks Manager online", array(
        //     'lagger_timeout' => $this->lagger_timeout,
        //     'multithread' => $this->multithread,
        //     'max_runtime' => $this->max_runtime,
        //     'max_childs' => $this->max_childs
        // ));

    }

    public function get() {

        $em = $this->getEntityManager();

        return $em->getRepository('Comodojo\Extender\Orm\Entities\Queue')->findAll();

    }

    public function flush(array $queue) {

        $this->getEvents()->emit( new QueueEvent('flush', null, $queue) );

        $em = $this->getEntityManager();

        foreach ($queue as $record) {
            $em->remove($record);
        }

        $em->flush();

    }

    public function add(Request $request) {

        $em = $this->getEntityManager();

        $uid = $this->doAddRequest($request, $em);

        $em->flush();

        return $uid;

    }

    public function addBulk(array $queue) {

        $em = $this->getEntityManager();

        $records = [];

        foreach ($queue as $name => $request) {
            $records[] = $request instanceof Request ? $this->doAddRequest($request, $em) : false;
        }

        $em->flush();

        return $records;

    }

    protected function doAddRequest(Request $request, EntityManager $em) {

        $this->getEvents()->emit( new QueueEvent('add', $request) );

        $record = new Queue();
        $record->setName($request->getName())->setRequest($request);

        $em->persist($record);

        return $request->getUid();

    }

}
