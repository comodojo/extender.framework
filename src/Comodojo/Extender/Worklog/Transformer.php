<?php namespace Comodojo\Extender\Worklog;

use \Comodojo\Extender\Orm\Entities\Worklog;
use \League\Fractal\TransformerAbstract;

class Transformer extends TransformerAbstract {

    public function transform (Worklog $worklog) {

        return [
            'id' => (int) $worklog->getId(),
            'name' => $worklog->getName(),
            'uid' => $worklog->getUid(),
            'pid' => $worklog->getPid(),
            'jid' => $worklog->getJid(),
            'parent_uid' => $worklog->getParentUid(),
            'task' => $worklog->getTask(),
            'parameters' => $worklog->getParameters(), //->export(),
            'status' => $worklog->getStatus(),
            'result' => $worklog->getResult(),
            'start_time' => $worklog->getStartTime(),
            'end_time' => $worklog->getEndTime()
        ];

    }

}
