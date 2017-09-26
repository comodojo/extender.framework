<?php namespace Comodojo\Extender\Schedule;

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
            ->add('scheduler:refresh', [$socket_commands, 'schedulerRefresh'])
            ->add('scheduler:add', [$socket_commands, 'schedulerAdd'])
            ->add('scheduler:get', [$socket_commands, 'schedulerGet'])
            ->add('scheduler:getByName', [$socket_commands, 'schedulerGetByName'])
            ->add('scheduler:edit', [$socket_commands, 'schedulerEdit'])
            ->add('scheduler:remove', [$socket_commands, 'schedulerRemove'])
            ->add('scheduler:removeByName', [$socket_commands, 'schedulerRemoveByName'])
            ->add('scheduler:enable', [$socket_commands, 'schedulerEnable'])
            ->add('scheduler:enableByName', [$socket_commands, 'schedulerEnableByName'])
            ->add('scheduler:disable', [$socket_commands, 'schedulerDisable'])
            ->add('scheduler:disableByName', [$socket_commands, 'schedulerDisableByName'])
            ->add('scheduler:info', [$socket_commands, 'schedulerInfo']);

    }

}
