<?php namespace Comodojo\Extender\Transformers;

use \Comodojo\Extender\Orm\Entities\Worklog;
use \League\Fractal\TransformerAbstract;

class WorklogTransformer extends TransformerAbstract {

    public function transform (Worklog $worklog) {

        $parameters = $worklog->getParameters()->export();
        if ( isset($parameters['parent']) && $parameters['parent'] instanceof \Comodojo\Extender\Task\Result ) {
            $parameters['parent'] = $parameters['parent']->export();
        }

        return [
            'id' => (int) $worklog->getId(),
            'name' => $worklog->getName(),
            'uid' => $worklog->getUid(),
            'pid' => $worklog->getPid(),
            'jid' => $worklog->getJid(),
            'parent_uid' => $worklog->getParentUid(),
            'task' => $worklog->getTask(),
            'parameters' => $parameters,
            'status' => $worklog->getStatus(),
            'result' => $worklog->getResult(),
            'start_time' => $worklog->getStartTime(),
            'end_time' => $worklog->getEndTime()
        ];

    }

}
