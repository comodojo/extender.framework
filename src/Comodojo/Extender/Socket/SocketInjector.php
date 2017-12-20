<?php namespace Comodojo\Extender\Socket;

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

        $schedule_commands = new ScheduleCommands();
        $queue_commands = new QueueCommands();
        $worklog_commands = new WorklogCommands();

        $commands
            // add schedule commands
            ->add('scheduler:refresh', [$schedule_commands, 'schedulerRefresh'])
            ->add('scheduler:add', [$schedule_commands, 'schedulerAdd'])
            ->add('scheduler:get', [$schedule_commands, 'schedulerGet'])
            ->add('scheduler:getByName', [$schedule_commands, 'schedulerGetByName'])
            ->add('scheduler:edit', [$schedule_commands, 'schedulerEdit'])
            ->add('scheduler:remove', [$schedule_commands, 'schedulerRemove'])
            ->add('scheduler:removeByName', [$schedule_commands, 'schedulerRemoveByName'])
            ->add('scheduler:enable', [$schedule_commands, 'schedulerEnable'])
            ->add('scheduler:enableByName', [$schedule_commands, 'schedulerEnableByName'])
            ->add('scheduler:disable', [$schedule_commands, 'schedulerDisable'])
            ->add('scheduler:disableByName', [$schedule_commands, 'schedulerDisableByName'])
            ->add('scheduler:info', [$schedule_commands, 'schedulerInfo'])
            // add queue commands
            ->add('queue:add', [$queue_commands, 'queueAdd'])
            ->add('queue:addBulk', [$queue_commands, 'queueAddBulk'])
            ->add('queue:info', [$queue_commands, 'queueInfo'])
            // add worklog commands
            ->add('worklog:count', [$worklog_commands, 'count'])
            ->add('worklog:list', [$worklog_commands, 'list'])
            ->add('worklog:getById', [$worklog_commands, 'byId'])
            ->add('worklog:getByJid', [$worklog_commands, 'byJid'])
            ->add('worklog:getByUid', [$worklog_commands, 'byUid'])
            ->add('worklog:getByPid', [$worklog_commands, 'byPid'])
            ->add('worklog:getByUid', [$worklog_commands, 'byUid']);
    }

}
