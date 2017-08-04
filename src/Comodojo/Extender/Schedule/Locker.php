<?php namespace Comodojo\Extender\Schedule\Locker;

use \Comodojo\Daemon\Locker\AbstractLocker;
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

class Locker extends AbstractLocker {

    /**
     * Lock file name
     *
     * @var string
     */
    private $lockfile = "schedule.plan";

    public function __construct($lockfile = null) {

        if ( $lockfile !== null ) $this->lockfile = $lockfile;

    }

    public function lock($timestamp) {

        return self::writeLock($this->lockfile, $timestamp);

    }

    public function update($timestamp) {

        return $this->lock($timestamp);

    }

    public function release() {

        return self::releaseLock($this->lockfile);

    }

}
