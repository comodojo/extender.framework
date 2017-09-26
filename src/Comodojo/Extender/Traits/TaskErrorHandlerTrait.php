<?php namespace Comodojo\Extender\Traits;

use \Comodojo\Foundation\Utils\ErrorLevelConverter;

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

trait TaskErrorHandlerTrait {

    public function installErrorHandler() {

        set_error_handler([$this, 'customErrorHandler']);

    }

    public function restoreErrorHandler() {

        restore_error_handler();

    }

    public function customErrorHandler($errno, $errstr, $errfile, $errline) {

        $error = ErrorLevelConverter::convert($errno);

        $this->getLogger()->error("Unhandled $error ($errno): $errstr [in $errfile line $errline]");

        return true;

    }

}
