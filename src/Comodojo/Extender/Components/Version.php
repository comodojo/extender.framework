<?php namespace Comodojo\Extender\Components;

use \Comodojo\Foundation\Base\AbstractVersion;

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

class Version extends AbstractVersion {

    protected $name = 'Comodojo/extender';
    protected $description = 'Daemonizable, database driven, multiprocess, (pseudo) cron task scheduler';
    protected $version = '2.0-dev';
    protected $ascii = "\r\n   ____     __              __       \r\n".
                       "  / __/_ __/ /____ ___  ___/ /__ ____\r\n".
                       " / _/ \ \ / __/ -_) _ \/ _  / -_) __/\r\n".
                       "/___//_\_\\__/\__/_//_/\_,_/\__/_/   \r\n";
    protected $template = "\n\n{ascii}\r\n".
                          "---------------------------------------------\r\n".
                          "{name} (ver {version})\r\n{description}\r\n";
    protected $prefix = 'extender-';

}
