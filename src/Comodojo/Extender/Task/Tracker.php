<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Psr\Log\LoggerInterface;

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

class Tracker {

    use ConfigurationTrait;
    use LoggerTrait;

    /**
     * @var array
     */
    private $queued = [];

    /**
     * @var array
     */
    private $running = [];

    /**
     * @var array
     */
    private $completed = [];

    /**
     * Tracker constructor
     *
     * @param string $name
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     */
    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);

    }

    public function getQueued() {

        return $this->queued;

    }

    public function setQueued(Request $request) {

        $uid = $request->getUid();

        $this->logger->debug("Adding task with uid $uid to queue");

        $this->queued[$uid] = $request;

        return $this;

    }

    public function countQueued() {

        return count($this->queued);

    }

    public function getRunning() {

        return $this->running;

    }

    public function setRunning($uid, $pid) {

        $request = $this->queued[$uid];

        $this->logger->debug("Task ".$request->getName()." (uid $uid) is starting with pid $pid");

        $request->setPid($pid);
        $request->setStartTimestamp(microtime(true));

        $this->running[$uid] = $request;

        unset($this->queued[$uid]);

        return $this;

    }

    public function countRunning() {

        return count($this->running);

    }

    public function getCompleted() {

        return $this->completed;

    }

    public function countCompleted() {

        return count($this->completed);

    }

    public function setCompleted($uid, Result $result) {

        $request = $this->running[$uid];

        $this->logger->debug("Task ".$request->getName()." (uid $uid) completed with ".($result->success ? 'success' : 'error'));

        $this->completed[$uid] = $result;

        unset($this->running[$uid]);

        return $this;

    }

    public function setAborted($uid, Result $result) {

        $request = $this->queued[$uid];

        $this->logger->debug("Task ".$request->getName()." (uid $uid) aborted: ".$result->message);

        $this->completed[$uid] = $result;

        unset($this->queued[$uid]);

        return $this;

    }

    public function getSucceeded() {

        return array_filter($this->completed, function($result) {
            return $result->success;
        });

    }

    public function countSucceeded() {

        return count($this->getSucceeded());

    }

    public function getFailed() {

        return array_filter($this->completed, function($result) {
            return !$result->success;
        });

    }

    public function countFailed() {

        return count($this->getFailed());

    }

    public static function create(Configuration $configuration, LoggerInterface $logger) {

        return new Tracker($configuration, $logger);

    }

}
