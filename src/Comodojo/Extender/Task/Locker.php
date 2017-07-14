<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\Configuration;
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

    private $running_jobs = [];

    private $completed_jobs = [];

    private $queued_jobs = [];

    private $lock_file = 'extender.tasks.locker';

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        $this->setConfiguration($configuration);
        $this->setLogger($logger);

        $lock_file = $configuration->get('queue-file');
        if ( $lock_file !== null ) $this->$lock_file = $lock_file;

    }

    public function isQueued(Job $job) {

        $this->logger->debug('Adding job '.$job->name.' (uid '.$job->uid.') to queue');

        $uid = $job->uid;

        $this->queued_jobs[$uid] = $job;

        $this->dump();

        return true;

    }

    public function isStarting($uid, $pid) {

        $job = $this->queued_jobs[$uid];

        $this->logger->debug('Job '.$job->name.' (uid '.$job->uid.') is starting with pid '.$pid);

        $job->pid = $pid;

        $job->start_timestamp = microtime(true);

        $this->running_jobs[$uid] = $job;

        unset($this->queued_jobs[$uid]);

        $this->dump();

        return $this;

    }

    public function isCompleted($uid, $success, $result, $wid = null) {

        $job = $this->running_jobs[$uid];

        $this->logger->debug('Job '.$job->name.' (uid '.$job->uid.') completed with '.($success ? 'success' : 'error'));

        $job->success = $success;

        $job->result = $result;

        $job->wid = $wid;

        $job->end_timestamp = microtime(true);

        $this->completed_jobs[$uid] = $job;

        unset($this->running_jobs[$uid]);

        $this->dump();

        return $this;

    }

    public function isAborted($uid, $error) {

        $job = $this->running_jobs[$uid];

        $this->logger->debug('Job '.$job->name.' (uid '.$job->uid.') aborted, reason: '.$error);

        $job->success = false;

        $job->result = $error;

        $job->wid = null;

        $job->end_timestamp = microtime(true);

        $this->completed_jobs[$uid] = $job;

        unset($this->queued_jobs[$uid]);

        $this->dump();

        return $this;

    }

    public function queued() {

        return $this->queued_jobs;

    }

    public function running() {

        return $this->running_jobs;

    }

    public function completed() {

        return $this->completed_jobs;

    }

    public function free() {

        $this->queued_jobs = array();
        $this->running_jobs = array();
        $this->completed_jobs = array();

        $this->dump();

    }

    public function release() {

        $lock = file_exists($this->queue_file) ? unlink($this->queue_file) : true;

        return $lock;

    }

    private function dump() {

        $data = array(
            'QUEUED' => count($this->queued_jobs),
            'RUNNING' => count($this->running_jobs),
            'COMPLETED' => count($this->completed_jobs)
        );

        $content = serialize($data);

        return file_put_contents($this->queue_file, $content);

    }

}
