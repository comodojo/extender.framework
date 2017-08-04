<?php namespace Comodojo\Extender\Traits;

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

trait RequestEntityTrait {

    /**
     * @var Request
     *
     * @ORM\Column(name="request", type="object", nullable=false)
     */
    protected $request;

    /**
     * Get desired process niceness
     *
     * @return Request
     */
    public function getRequest() {

        return $this->request;

    }

    /**
     * Set desired process niceness
     *
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request) {

        $this->request = $request;

        return $this;

    }

}
