<?php namespace Comodojo\Extender\Traits;

use \Comodojo\Extender\Task\TaskParameters;

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

trait WorkerTrait {

    protected $task_manager;

    protected $job_manager;

    public function getTaskManager() {

        return $this->task_manager;

    }

    public function getJobManager() {

        return $this->job_manager;

    }

    protected function jobsToRequests(array $jobs) {

        return array_map(function($job) {

            $request = $job->getRequest();

            if ( $job instanceof \Comodojo\Extender\Orm\Entities\Schedule ) {
                $request->setJid($job);
            }

            return $request;
        }, $jobs);

    }

}
