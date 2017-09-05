<?php namespace Comodojo\Extender\Task;

use \Comodojo\Daemon\Locker\AbstractLocker;
use \Comodojo\Daemon\Utils\ProcessTools;

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

class Locker extends AbstractLocker {

    protected $usage = [
        'STARTTIMESTAMP' => null,
        'PID' => null,
        'MEMORYUSAGE' => null,
        'MEMORYPEAKUSAGE' => null
    ];

    protected $counters = [
        'QUEUED' => 0,
        'RUNNING' => 0,
        'COMPLETED' => 0,
        'SUCCEEDED' => 0,
        'FAILED' => 0,
        'ABORTED' => 0
    ];

    /**
     * Lock file name
     *
     * @var string
     */
    private $lockfile;

    public function __construct($lockfile) {

        $this->lockfile = $lockfile;
        $this->usage_data['STARTTIMESTAMP'] = time();
        $this->usage_data['PID'] = ProcessTools::getPid();

    }

    public function getQueued() {

        return $this->counters['QUEUED'];

    }

    public function getRunning() {

        return $this->counters['RUNNING'];

    }

    public function getCompleted() {

        return $this->counters['COMPLETED'];

    }

    public function getSucceeded() {

        return $this->counters['SUCCEEDED'];

    }

    public function getFailed() {

        return $this->counters['FAILED'];

    }

    public function getAborted() {

        return $this->counters['ABORTED'];

    }

    public function lock($what) {

        $usage = $this->updateUsage();
        $counters = $this->updateCounters($what);

        $data = serialize([
            'USAGE' => $usage,
            'COUNTERS' => $counters
        ]);

        return self::writeLock($this->lockfile, $data);

    }

    public function release() {

        return self::releaseLock($this->lockfile);

    }

    protected function updateUsage() {

        $this->usage_data['MEMORYUSAGE'] = memory_get_usage();
        $this->usage_data['MEMORYPEAKUSAGE'] = memory_get_peak_usage();

        return $this->usage_data;

    }

    protected function updateCounters($counters) {

        if ( isset($counters['QUEUED']) && is_int($counters['QUEUED']) ) $this->counters['QUEUED'] = $counters['QUEUED'];
        if ( isset($counters['RUNNING']) && is_int($counters['RUNNING']) ) $this->counters['RUNNING'] = $counters['RUNNING'];
        if ( !empty($counters['COMPLETED']) ) $this->counters['COMPLETED'] = $this->counters['COMPLETED'] + (int) $counters['COMPLETED'];
        if ( !empty($counters['SUCCEEDED']) ) $this->counters['SUCCEEDED'] = $this->counters['SUCCEEDED'] + (int) $counters['SUCCEEDED'];
        if ( !empty($counters['FAILED']) ) $this->counters['FAILED'] = $this->counters['FAILED'] + (int) $counters['FAILED'];
        if ( !empty($counters['ABORTED']) ) $this->counters['ABORTED'] = $this->counters['ABORTED'] + (int) $counters['ABORTED'];

        // array_walk($this->counters, function(&$counter, $name) use ($counters) {
        //
        //     if ( !empty($counters[$name]) ) $counter = $counter + (int) $counters[$name];
        //
        // });

        return $this->counters;

    }

}
