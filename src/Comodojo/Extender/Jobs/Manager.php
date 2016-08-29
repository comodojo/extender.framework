<?php namespace Comodojo\Extender\Jobs;

use \Comodojo\Dispatcher\Components\Configuration;
use \Psr\Log\LoggerInterface;

/**
 * Job object
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

class Manager {

    private $logger;

    private $running_jobs = array();

    private $completed_jobs = array();

    private $queued_jobs = array();

    private $queue_file = 'extender.queue';

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        $queue_file = $configuration->get('queue-file');

        if ( $queue_file !== null ) $this->queue_file = $queue_file;

        $this->logger = $logger;

    }

    public function isQueued(Job $job) {

        $this->logger->debug('Adding job '.$job->name.' (uid '.$job->uid.') to queue');

        $uid = $job->uid;

        $this->queued_jobs[$uid] = $job;

        $this->dump();

        return true;

    }

    public function isStarting($uid, $pid) {

        $job = $this->queued_jobs[$uid];

        $this->logger->debug('Job '.$job->name.' (uid '.$job->uid.') is starting with pid '.$pid);

        $job->pid = $pid;

        $job->start_timestamp = microtime(true);

        $this->running_jobs[$uid] = $job;

        unset($this->queued_jobs[$uid]);

        $this->dump();

        return $this;

    }

    public function isCompleted($uid, $success, $result, $wid = null) {

        $job = $this->running_jobs[$uid];

        $this->logger->debug('Job '.$job->name.' (uid '.$job->uid.') completed with '.($success ? 'success' : 'error'));

        $job->success = $success;

        $job->result = $result;

        $job->wid = $wid;

        $job->end_timestamp = microtime(true);

        $this->completed_jobs[$uid] = $job;

        unset($this->running_jobs[$uid]);

        $this->dump();

        return $this;

    }

    public function isAborted($uid, $error) {

        $job = $this->running_jobs[$uid];

        $this->logger->debug('Job '.$job->name.' (uid '.$job->uid.') aborted, reason: '.$error);

        $job->success = false;

        $job->result = $error;

        $job->wid = null;

        $job->end_timestamp = microtime(true);

        $this->completed_jobs[$uid] = $job;

        unset($this->queued_jobs[$uid]);

        $this->dump();

        return $this;

    }

    public function queued() {

        return $this->queued_jobs;

    }

    public function running() {

        return $this->running_jobs;

    }

    public function completed() {

        return $this->completed_jobs;

    }

    public function free() {

        $this->queued_jobs = array();
        $this->running_jobs = array();
        $this->completed_jobs = array();

        $this->dump();

    }

    public function release() {

        $lock = file_exists($this->queue_file) ? unlink($this->queue_file) : true;

        return $lock;

    }

    private function dump() {

        $data = array(
            'QUEUED' => count($this->queued_jobs),
            'RUNNING' => count($this->running_jobs),
            'COMPLETED' => count($this->completed_jobs)
        );

        $content = serialize($data);

        return file_put_contents($this->queue_file, $content);

    }

}
