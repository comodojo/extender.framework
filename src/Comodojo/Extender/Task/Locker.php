<?php namespace Comodojo\Extender\Task;

use \Comodojo\Extender\Traits\ConfigurationTrait;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Daemon\Traits\LoggerTrait;
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

class Locker {

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
     * @var string
     */
    private $lock_file;

    /**
     * Manager constructor
     *
     * @param string $name
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     */
    public function __construct($name, Configuration $configuration, LoggerInterface $logger) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);

        $base_path = $configuration->get('base-path');
        $lock_path = $configuration->get('run-path');
        $this->lock_file = "$base_path/$lock_path/$name.lock";

    }

    public function getQueued() {

        return $this->queued;

    }

    public function setQueued(Request $request) {

        $uid = $request->getUid();

        $this->logger->debug("Adding task with uid $uid to queue");

        $this->queued[$uid] = $request;

        $this->dump();

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

        $this->dump();

        return $this;

    }

    public function countRunning() {

        return count($this->running);

    }

    public function getCompleted() {

        return $this->completed;

    }

    public function setCompleted($uid, Result $result) {

        $request = $this->running[$uid];

        $this->logger->debug("Task ".$request->getName()." (uid $uid) completed with ".($result->success ? 'success' : 'error'));

        $this->completed[$uid] = $result;

        unset($this->running[$uid]);

        $this->dump();

        return $this;

    }

    public function setAborted($uid, Result $result) {

        $request = $this->queued[$uid];

        $this->logger->debug("Task ".$request->getName()." (uid $uid) aborted: ".$result->message);

        $this->completed[$uid] = $result;

        unset($this->queued[$uid]);

        $this->dump();

        return $this;

    }

    public function getSucceeded() {

        return array_filter($this->completed, function($result) {
            return $result->success;
        });

    }

    public function getFailed() {

        return array_filter($this->completed, function($result) {
            return !$result->success;
        });

    }

    public function freeCompleted() {

        $this->completed = array_map(function($result) {
            return $result->success;
        }, $this->completed);

    }

    public function free() {

        $this->queued = [];
        $this->running = [];
        $this->completed = [];

        $this->dump();

    }

    public function release() {

        $lock = file_exists($this->lock_file) ? unlink($this->lock_file) : true;

        return $lock;

    }

    private function dump() {

        return @file_put_contents($this->lock_file, serialize(
            [
                'QUEUED' => count($this->getQueued()),
                'RUNNING' => count($this->getRunning()),
                'COMPLETED' => count($this->getCompleted()),
                'SUCCEEDED' => count($this->getSucceeded()),
                'FAILED' => count($this->getFailed())
            ]
        ));

    }

    public static function create($name, Configuration $configuration, LoggerInterface $logger) {

        return new Locker($name, $configuration, $logger);

    }

}
