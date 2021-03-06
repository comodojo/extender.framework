<?php namespace Comodojo\Extender\Events;

use \Comodojo\Foundation\Events\AbstractEvent;
use \Comodojo\Extender\Orm\Entities\Schedule;

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

class ScheduleEvent extends AbstractEvent {

    private $schedule;

    private $parent_schedule;

    public function __construct($event, Schedule $schedule, Schedule $parent_schedule = null) {

        parent::__construct("extender.schedule.$event");

        $this->schedule = $schedule;
        $this->parent_schedule = $parent_schedule;

    }

    public function getSchedule() {

        return $this->schedule;

    }

    public function getParentSchedule() {

        return $this->parent_schedule;

    }

}
