<?php namespace Comodojo\Extender\Queue;

use \Comodojo\Daemon\Socket\Commands;

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

class SocketInjector {

    public static function inject(Commands $commands) {

        $socket_commands = new SocketCommands();

        $commands
            ->add('queue:add', [$socket_commands, 'queueAdd'])
            ->add('queue:addBulk', [$socket_commands, 'queueAddBulk'])
            ->add('queue:info', [$socket_commands, 'queueInfo']);

    }

}
