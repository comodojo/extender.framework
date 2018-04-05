<?php namespace Comodojo\Extender\Socket;

use \Comodojo\Daemon\Daemon as AbstractDaemon;
use \Comodojo\RpcServer\RpcMethod;

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

    public static function inject(AbstractDaemon $daemon) {

        $mmanager = $daemon->getSocket()->getRpcServer()->getMethods();
        $errors = $daemon->getSocket()->getRpcServer()->getErrors();

        $errors->add(-31002, "No record could be found");

        // ******************
        // Scheduler Commands
        // ******************

        $mmanager->add(
            RpcMethod::create(
                "scheduler.refresh",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Refresh::execute',
                 $daemon)
            ->setDescription("Refresh current scheduler's schedule")
            ->setReturnType('boolean')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.info",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Info::execute',
                 $daemon)
            ->setDescription("Show current scheduler status")
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.add",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Add::execute',
                 $daemon)
            ->setDescription("Set new schedule")
            ->addParameter('struct', 'schedule')
            ->addParameter('struct', 'request')
            ->setReturnType('int')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.list",
                 'Comodojo\Extender\Socket\Commands\Scheduler\GetList::execute',
                 $daemon)
            ->setDescription("List all installed schedules")
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.get",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Get::execute',
                 $daemon)
            ->setDescription("Get a schedule and related task request by id (int) or name (string)")
            ->addParameter('int', 'id')
            ->setReturnType('array')
            ->addSignature()
            ->addParameter('string', 'name')
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.edit",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Edit::execute',
                 $daemon)
            ->setDescription("Edit a schedule")
            ->addParameter('struct', 'schedule')
            ->addParameter('struct', 'request')
            ->setReturnType('boolean')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.disable",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Disable::execute',
                 $daemon)
            ->setDescription("Disable a schedule by id (int) or name (string)")
            ->addParameter('int', 'id')
            ->setReturnType('boolean')
            ->addSignature()
            ->addParameter('string', 'name')
            ->setReturnType('boolean')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.enable",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Enable::execute',
                 $daemon)
            ->setDescription("Enable a schedule by id (int) or name (string)")
            ->addParameter('int', 'id')
            ->setReturnType('boolean')
            ->addSignature()
            ->addParameter('string', 'name')
            ->setReturnType('boolean')
        );

        $mmanager->add(
            RpcMethod::create(
                "scheduler.remove",
                 'Comodojo\Extender\Socket\Commands\Scheduler\Remove::execute',
                 $daemon)
            ->setDescription("Remove a schedule by id (int) or name (string)")
            ->addParameter('int', 'id')
            ->setReturnType('boolean')
            ->addSignature()
            ->addParameter('string', 'name')
            ->setReturnType('boolean')
        );

        // **************
        // Queue Commands
        // **************

        $mmanager->add(
            RpcMethod::create(
                "queue.info",
                 'Comodojo\Extender\Socket\Commands\Queue\Info::execute',
                 $daemon)
            ->setDescription("Get current queue status")
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "queue.add",
                 'Comodojo\Extender\Socket\Commands\Queue\Add::execute',
                 $daemon)
            ->setDescription("Push new request to queue")
            ->addParameter('struct', 'request')
            ->setReturnType('string')
        );

        $mmanager->add(
            RpcMethod::create(
                "queue.addBulk",
                 'Comodojo\Extender\Socket\Commands\Queue\AddBulk::execute',
                 $daemon)
            ->setDescription("Push new requests to queue")
            ->addParameter('array', 'requests')
            ->setReturnType('array')
        );

        // ****************
        // Worklog Commands
        // ****************

        $mmanager->add(
            RpcMethod::create(
                "worklog.count",
                 'Comodojo\Extender\Socket\Commands\Worklog\Count::execute',
                 $daemon)
            ->setDescription("Count recorded worklogs")
            ->setReturnType('int')
        );

        $mmanager->add(
            RpcMethod::create(
                "worklog.list",
                 'Comodojo\Extender\Socket\Commands\Worklog\GetList::execute',
                 $daemon)
            ->setDescription("Get a list of worklog according to (optional) filter")
            ->setReturnType('array')
            ->addSignature()
            ->addParameter('struct', 'filter')
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "worklog.byId",
                 'Comodojo\Extender\Socket\Commands\Worklog\ById::execute',
                 $daemon)
            ->setDescription("Get a worklog from id")
            ->addParameter('int', 'id')
            ->setReturnType('struct')
        );

        $mmanager->add(
            RpcMethod::create(
                "worklog.byUid",
                 'Comodojo\Extender\Socket\Commands\Worklog\ByUid::execute',
                 $daemon)
            ->setDescription("Get a worklog from uid")
            ->addParameter('string', 'uid')
            ->setReturnType('struct')
        );

        $mmanager->add(
            RpcMethod::create(
                "worklog.byJid",
                 'Comodojo\Extender\Socket\Commands\Worklog\ByJid::execute',
                 $daemon)
            ->setDescription("Get a list of worklog from job id according to (optional) filter")
            ->addParameter('int', 'jid')
            ->setReturnType('array')
            ->addSignature()
            ->addParameter('int', 'jid')
            ->addParameter('struct', 'filter')
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "worklog.byPid",
                 'Comodojo\Extender\Socket\Commands\Worklog\ByPid::execute',
                 $daemon)
            ->setDescription("Get a list of worklog from system pid according to (optional) filter")
            ->addParameter('int', 'pid')
            ->setReturnType('array')
            ->addSignature()
            ->addParameter('int', 'pid')
            ->addParameter('struct', 'filter')
            ->setReturnType('array')
        );

        $mmanager->add(
            RpcMethod::create(
                "worklog.byPuid",
                 'Comodojo\Extender\Socket\Commands\Worklog\ByPuid::execute',
                 $daemon)
            ->setDescription("Get worklogs from parent uid")
            ->addParameter('string', 'puid')
            ->setReturnType('array')
        );

    }

}
