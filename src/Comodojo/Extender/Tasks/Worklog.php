<?php namespace Comodojo\Extender\Tasks;

use \Comodojo\Dispatcher\Components\Configuration;
use \Psr\Log\LoggerInterface;
use \Doctrine\DBAL\Connection;

/**
 * Task object
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class Worklog {

    private $dbh;

    private $table;

    public function __construct(Connection $dbh, $table) {

        $this->dbh = $dbh;

        $this->table = $table;

    }

    public function open($pid, $name, $jobid, $task, $start) {

        $this->dbh
            ->createQueryBuilder()
            ->insert($this->table)
            ->values(array (
                'status' => 'RUNNING',
                'pid' => '?',
                'name' => '?',
                'jobid' => '?',
                'task' => '?',
                'start' => '?'
            ))
            ->setParameter(0, $pid)
            ->setParameter(1, $name)
            ->setParameter(2, $jobid)
            ->setParameter(3, $task)
            ->setParameter(4, $start)
            ->getQuery()
            ->execute();

        return $this->dbh->lastInsertId();

    }

    public function close($wid, $success, $result, $end) {

        $this->dbh
            ->createQueryBuilder()
            ->update($this->table)
            ->where("id = $wid")
            ->values(array (
                'status' => 'FINISHED',
                'success' => '?',
                'result' => '?',
                'end' => '?'
            ))
            ->setParameter(0, $success)
            ->setParameter(1, $result)
            ->setParameter(2, $end)
            ->getQuery()
            ->execute();

    }

}
