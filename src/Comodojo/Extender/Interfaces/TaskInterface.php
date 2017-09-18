<?php namespace Comodojo\Extender\Interfaces;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Extender\Task\TaskParameters;
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

interface TaskInterface {

    /**
     * Task constructor
     *
     * @param string $name
     * @param TaskParameters $parameters
     * @param LoggerInterface $logger
     */
    public function __construct(
        Configuration $configuration,
        EventsManager $events,
        LoggerInterface $logger,
        $name,
        TaskParameters $parameters
    );

    /**
     * The run method; SHOULD be implemented by each task
     */
    public function run();

}
