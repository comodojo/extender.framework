<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\Base\ParametersTrait;

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
 * @ORM\Table(name="extender_queue")
 * @ORM\Entity
 */

class TaskParameters {

    use ParametersTrait;

    public function __construct(array $parameters = []) {

        $this->merge($parameters);

    }

    public function merge(array $properties) {

        $this->parameters = array_replace($this->parameters, $properties);

        return $this;

    }

    public function export() {

        return $this->parameters;

    }

}
