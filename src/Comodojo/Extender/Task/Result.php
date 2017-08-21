<?php namespace Comodojo\Extender\Task;

use \Comodojo\Foundation\DataAccess\Model;

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


class Result extends Model{

    protected $mode = self::READONLY;

    public function __construct($output) {

        $this->setRaw('uid', $output[0]);
        $this->setRaw('pid', $output[1]);
        $this->setRaw('jid', $output[2]);
        $this->setRaw('name', $output[3]);
        $this->setRaw('success', $output[4]);
        $this->setRaw('start', $output[5]);
        $this->setRaw('end', $output[6]);
        $this->setRaw('result', $output[7]);
        $this->setRaw('wid', $output[8]);

    }

}
