<?php namespace Comodojo\Extender\Utils;

use \DateTime;
use \DateInterval;

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

class StopWatch {

    /**
     * @var DateTime
     */
    protected $start_time;

    /**
     * @var DateTime
     */
    protected $stop_time;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @return StopWatch
     */
    public function start() {

        $this->start_time = self::capture();
        $this->active = true;

        return $this;

    }

    /**
     * @return StopWatch
     */
    public function stop() {

        $this->stop_time = self::capture();
        $this->active = false;

        return $this;

    }

    /**
     * @return StopWatch
     */
    public function resume() {

        $this->stop_time = null;
        $this->active = true;

        return $this;


    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->active;
    }

    /**
     * @return DateTime
     */
    public function getStartTime() {

        return $this->start_time;

    }

    /**
     * @return DateTime
     */
    public function getStopTime() {

        return $this->stop_time;

    }

    /**
     * @return DateInterval
     */
    public function getDrift() {

        return is_null($this->start_time) || is_null($this->stop_time) ? null : $this->stop_time->diff($this->start_time);

    }

    /**
     * @return StopWatch
     */
    public function clear() {

        $this->start_time = null;
        $this->stop_time = null;
        $this->active = false;

        return $this;

    }

    /**
     * @return DateTime
     */
    final public static function capture() {

        $t = microtime(true);

        $micro = sprintf("%06d",($t - floor($t)) * 1000000);

        return new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

    }

}
