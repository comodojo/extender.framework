<?php namespace Comodojo\Extender\Orm\Entities;

use \Doctrine\ORM\Mapping as ORM;
use \Comodojo\Extender\Traits\BaseEntityTrait;
use \Comodojo\Extender\Traits\ProcessEntityTrait;
use \Cron\CronExpression;
use \Comodojo\Foundation\Validation\DataFilter;
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
 * @ORM\Table(name="extender_schedule")
 * @ORM\Entity
 */

class Schedule {

    use BaseEntityTrait;
    use ProcessEntityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="min", type="string", length=16, nullable=false)
     */
    protected $min;

    /**
     * @var string
     *
     * @ORM\Column(name="hour", type="string", length=16, nullable=false)
     */
    protected $hour;

    /**
     * @var string
     *
     * @ORM\Column(name="day", type="string", length=16, nullable=false)
     */
    protected $day;

    /**
     * @var string
     *
     * @ORM\Column(name="month", type="string", length=16, nullable=false)
     */
    protected $month;

    /**
     * @var string
     *
     * @ORM\Column(name="weekday", type="string", length=16, nullable=false)
     */
    protected $weekday;

    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", length=16, nullable=false)
     */
    protected $year;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    protected $enabled = 0;

    /**
     * @var datetime
     *
     * @ORM\Column(name="firstrun", type="datetime", nullable=true)
     */
    protected $firstrun;

    /**
     * @var datetime
     *
     * @ORM\Column(name="lastrun", type="datetime", nullable=true)
     */
    protected $lastrun;

    /**
     * Get a brief job description
     *
     * @return string
     */
    public function getDescription() {

        return $this->description;

    }

    /**
     * Set brief job description
     *
     * @param string $description
     * @return Schedule
     */
    public function setDescription($description) {

        $this->description = $description;

        return $this;

    }

    /**
     * Get cron expression of this schedule
     *
     * @return string
     */
    public function getExpression() {

        return (string) $this->buildExpression();

    }

    /**
     * set cron expression for this schedule
     *
     * @param srting $expression A cron-compatible expression
     * @return Schedule
     */
    public function setExpression($expression) {

        $exp = CronExpression::factory($expression);

        $this->minute = $exp->getExpression(0);
        $this->hour = $exp->getExpression(1);
        $this->day = $exp->getExpression(2);
        $this->month = $exp->getExpression(3);
        $this->weekday = $exp->getExpression(4);
        $this->year = $exp->getExpression(5);

        return $this;

    }

    /**
     * True if job is currently enabled
     *
     * @return bool
     */
    public function getEnabled() {

        return $this->enabled;

    }

    /**
     * Set enable/disable status
     *
     * @param bool $enable
     * @return Schedule
     */
    public function setEnable($enable) {

        $ena = DataFilter::filterBoolean($enable);

        $this->enable = $ena;

        return $this;

    }

    /**
     * Get the first-run-date of job
     *
     * @return DateTime
     */
    public function getFirstrun() {

        return $this->firstrun;

    }

    /**
     * Set the first-run-date of job
     *
     * @param DateTime $datetime
     * @return Schedule
     */
    public function setFirstrun(DateTime $datetime) {

        $this->firstrun = $datetime;

        return $this;

    }

    /**
     * Get the first-run-date of job
     *
     * @return DateTime
     */
    public function getLastrun() {

        return $this->lastrun;

    }

    /**
     * Set the first-run-date of job
     *
     * @param DateTime $datetime
     * @return Schedule
     */
    public function setLastrun(DateTime $datetime) {

        $this->lastrun = $datetime;

        return $this;

    }

    public function shouldRunJob(DateTime $time) {

        $last_run = $this->getLastrun();

        if ( is_null($lastrun) ) {
            $next_run = $this->getFirstrun();
        } else {
            $next_run = $this->buildExpression()->getNextRunDate($last_run);
        }

        return $time < $next_run;

    }

    public function getNextPlannedRun(DateTime $time) {

        $last_run = $this->getLastrun();

        if ( is_null($lastrun) ) {
            $next_run = $this->getFirstrun();
        } else {
            $next_run = $this->buildExpression()->getNextRunDate($time);
        }

        return $next_run;

    }

    protected function buildExpression() {

        $exp_string = implode(" ", [
            $this->minute,
            $this->hour,
            $this->day,
            $this->month,
            $this->weekday,
            $this->year
        ]);

        return CronExpression::factory($exp_string);

    }
}
