<?php namespace Comodojo\Extender\Socket\Commands\Worklog;

use \Comodojo\Daemon\Daemon;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Extender\Worklog\Manager;
use \Comodojo\Extender\Socket\Messages\Worklog\Filter;
use \Comodojo\Extender\Transformers\WorklogTransformer;
use \League\Fractal\Manager as FractalManager;
use \League\Fractal\Resource\Collection;

class GetList {

    public static function execute(Parameters $params, Daemon $daemon) {

        $filter = $params->get('filter');

        $manager = new Manager(
            $daemon->getConfiguration(),
            $daemon->getLogger(),
            $daemon->getEvents()
        );

        if ( empty($filter) ) {
            $data = $manager->get();
        } else {
            $f = Filter::createFromExport($filter);
            $data = $manager->get(
                [],
                $f->getLimit(),
                $f->getOffset(),
                $f->getReverse()
            );
        }

        $resource = new Collection($data, new WorklogTransformer);
        $fractal = new FractalManager();
        $data = $fractal->createData($resource)->toArray();

        return $data['data'];

    }

}
