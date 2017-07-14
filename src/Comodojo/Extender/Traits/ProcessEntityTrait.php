<?php namespace Comodojo\Extender\Traits;

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

trait ProcessEntityTrait {

    /**
     * @var integer
     *
     * @ORM\Column(name="niceness", type="integer", nullable=false)
     */
    protected $niceness = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="maxtime", type="integer", nullable=false)
     */
    protected $maxtime = 600;

    /**
     * Get desired process niceness
     *
     * @return int
     */
    public function getNiceness() {

        return $this->niceness;

    }

    /**
     * Set desired process niceness
     *
     * @param int $niceness An integer between -20 and +20, default 0
     * @return Schedule
     */
    public function setNiceness($niceness) {

        $niceness = DataFilter::filterInteger($niceness, -20, 20);

        $this->niceness = $niceness;

        return $this;

    }

    /**
     * Get maximum time allowed for process to terminate
     *
     * @return int
     */
    public function getMaxtime() {

        return $this->maxtime;

    }

    /**
     * Set maximum time given to process to terminate
     *
     * @param int $secs Seconds as an integer >0, default 600 (10 minutes)
     * @return Schedule
     */
    public function setMaxtime($secs) {

        $secs = DataFilter::filterInteger($secs, 1, PHP_INT_MAX, 600);

        $this->maxtime = $secs;

        return $this;

    }

}
