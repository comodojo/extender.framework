<?php namespace Comodojo\Extender\Orm\Entities;

use \Doctrine\ORM\Mapping as ORM;
use \Comodojo\Extender\Traits\BaseEntityTrait;
use \InvalidArgumentException;
use \DateTime;

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
 *
 * @ORM\Table(name="extender_worklog")
 * @ORM\Entity
 */
class Worklog {

    const STATUS_RUNNING = 0;

    const STATUS_ERROR = 1;

    const STATUS_FINISHED = 2;

    const STATUS_ABORTED = 3;

    use BaseEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="pid", type="integer", nullable=false)
     */
    protected $pid;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Schedule", cascade={"persist"})
     * @ORM\JoinColumn(name="jid", nullable=true, referencedColumnName="id", onDelete="SET NULL")
     */
    protected $jid;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    protected $status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="success", type="boolean", nullable=true)
     */
    protected $success;

    /**
     * @var string
     *
     * @ORM\Column(name="result", type="text", length=65535, nullable=true)
     */
    protected $result;

    /**
     * @var datetime
     *
     * @ORM\Column(name="start", type="datetime", nullable=false)
     */
    protected $start_time;

    /**
     * @var datetime
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    protected $end_time;

    /**
     * Get worklog item's pid
     *
     * @return integer
     */
    public function getPid() {

        return $this->pid;

    }

    /**
     * Set worklog item's pid
     *
     * @param string $pid
     * @return Worklog
     */
    public function setPid($pid) {

        $this->pid = $pid;

        return $this;

    }

    /**
     * Get worklog item's jid
     *
     * @return integer
     */
    public function getJid() {

        return $this->jid;

    }

    /**
     * Set worklog item's jid
     *
     * @param string $jid
     * @return Worklog
     */
    public function setJid($jid) {

        $this->jid = $jid;

        return $this;

    }

    /**
     * Get current job status
     *
     * @return int
     */
    public function getStatus() {

        return $this->status;

    }

    /**
     * Set current job status
     *
     * @param string $name
     * @return Worklog
     */
    public function setStatus($status) {

        if ( !in_array($status, range(0, 3)) ) throw new InvalidArgumentException("Invalid status $status");

        $this->status = $status;

        return $this;

    }

    /**
     * True if task is finished with success state
     *
     * @return bool
     */
    public function getSuccess() {

        return $this->success;

    }

    /**
     * Set task exit status
     *
     * @param bool $success
     * @return Worklog
     */
    public function setSuccess($success) {

        $success = DataFilter::filterBoolean($success);

        $this->success = $success;

        return $this;

    }

    /**
     * Get the task's result
     *
     * @return string
     */
    public function getResult() {

        return unserialize($this->result);

    }

    /**
     * Set the task's result
     *
     * @param string $result
     * @return Worklog
     */
    public function setResult($result) {

        $this->result = serialize($result);

        return $this;

    }

    /**
     * Get the task's start-time
     *
     * @return DateTime
     */
    public function getStartTime() {

        return $this->start_time;

    }

    /**
     * Set the task's start-time
     *
     * @param DateTime $datetime
     * @return Worklog
     */
    public function setStartTime(DateTime $datetime) {

        $this->start_time = $datetime;

        return $this;

    }

    /**
     * Get the task's end-time
     *
     * @return DateTime
     */
    public function getEndTime() {

        return $this->end_time;

    }

    /**
     * Set the task's end-time
     *
     * @param DateTime $datetime
     * @return Worklog
     */
    public function setEndTime(DateTime $datetime) {

        $this->end_time = $datetime;

        return $this;

    }

}
