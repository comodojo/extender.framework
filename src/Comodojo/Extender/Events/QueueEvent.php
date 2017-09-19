<?php namespace Comodojo\Extender\Events;

use \Comodojo\Foundation\Events\AbstractEvent;
use \Comodojo\Extender\Task\Request;

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

class QueueEvent extends AbstractEvent {

    private $request;

    private $queue;

    public function __construct($event, Request $request = null, array $queue = []) {

        parent::__construct("extender.queue.$event");

        $this->request = $request;
        $this->queue = $queue;

    }

    public function getRequest() {

        return $this->request;

    }

    public function getQueue() {

        return $this->queue;

    }

}
