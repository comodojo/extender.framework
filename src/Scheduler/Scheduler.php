<?php namespace Comodojo\Extender\Scheduler;

use \Cron\CronExpression;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Extender\Cache;
use \Exception;

/**
 * Extender scheduler
 *
 * @package     Comodojo extender
 * @author      Marco Giovinazzi <info@comodojo.org>
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

class Scheduler {

    /**
     * Get planned schedules
     *
     * @param   Object  $logger
     * @param   float   $timestamp
     *
     * @return  array
     */
    final static public function getSchedules($logger, $timestamp) {

        $schedules = array();

        try {
            
            $jobs = self::getJobs();

            foreach ($jobs as $job) {

                if ( self::shouldRunJob($job, $logger, $timestamp) ) array_push($schedules, $job);
                
            }   

        } catch (DatabaseException $de) {

            $logger->error("Cannot load job list due to database error",array(
                "ERROR" => $de->getMessage(),
                "ERRID" => $de->getCode()
            ));
            
            throw $de;

        } catch (Exception $e) {
            
            $logger->error("Cannot load job list due to generic error",array(
                "ERROR" => $e->getMessage(),
                "ERRID" => $e->getCode()
            ));

            throw $e;

        }

        $logger->info("\n".sizeof($schedules)." job(s) in current queue");

        return $schedules;

    }

    /**
     * Update schedules (last run)
     *
     * @param   Object  $logger
     * @param   array   $completed_processes
     */
    final static public function updateSchedules($logger, $completed_processes) {

        if ( empty($completed_processes) ) {

            $logger->info("No schedule to update, exiting");

            return;

        }
        
        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->autoClean();

            foreach ($completed_processes as $process) {

                $db->table(EXTENDER_DATABASE_TABLE_JOBS)->keys("lastrun")->values($process[3])->where('id','=',$process[6])->update();

            }

        }
        catch (DatabaseException $de) {

            unset($db);

            throw $de;

        }

        unset($db);

        Cache::purge();

    }

    /**
     * Update sincle schedule (last run)
     *
     * @param   Object  $logger
     * @param   float   $lastrum
     */
    final static public function updateSchedule($name, $lastrun) {

        if ( empty($name) ) throw new Exception("Invalid job name");

        if ( empty($lastrun) ) throw new Exception("Empty job run datetime");
        
        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->keys("lastrun")->values($lastrun)->where('name','=',$name)->update();

        }
        catch (DatabaseException $de) {

            unset($db);

            throw $de;

        }

        unset($db);

        Cache::purge();

    }

    /**
     * Add a schedule
     *
     * @param   string  $expression
     * @param   string  $name
     * @param   string  $task
     * @param   string  $description
     * @param   array   $params
     *
     * @return  array
     */
    final static public function addSchedule($expression, $name, $task, $description=null, $params=array()) {

        if ( empty($name) ) throw new Exception("Invalid job name");

        if ( empty($task) ) throw new Exception("A job feels alone without a task");

        try {
            
            list( $next_calculated_run, $parsed_expression )= self::validateExpression($expression);

            list($min, $hour, $dayofmonth, $month, $dayofweek, $year) = $parsed_expression;

            var_dump($parsed_expression);

            $parameters = serialize($params);

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys(array("name", "task", "description",
                    "min", "hour", "dayofmonth", "month", 
                    "dayofweek", "year", "params"))
                ->values(array($name, $task, $description,
                    $min, $hour, $dayofmonth, $month,
                    $dayofweek, $year, $parameters))
                ->store();

        } catch (Exception $e) {
            
            throw $e;

        }

        Cache::purge();

        return array($result['id'], $next_calculated_run);

    }

    final static public function removeSchedule($name) {
        
        if ( empty($name) ) throw new Exception("Invalid or empty job name");

        try {
            
            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->where("name","=",$name)
                ->delete();

        } catch (Exception $e) {
            
            throw $e;

        }

        if ( $result['affected_rows'] == 0 ) return false;

        Cache::purge();

        return true;

    }

    final static public function enableSchedule($name) {

        if ( empty($name) ) throw new Exception("Invalid or empty job name");

        try {
            
            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys("enabled")
                ->values(true)
                ->where("name","=",$name)
                ->update();

        } catch (Exception $e) {
            
            throw $e;

        }

        Cache::purge();

        return true;

    }

    final static public function disableSchedule($name) {

        if ( empty($name) ) throw new Exception("Invalid or empty job name");

        try {
            
            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys("enabled")
                ->values(false)
                ->where("name","=",$name)
                ->update();

        } catch (Exception $e) {
            
            throw $e;

        }

        Cache::purge();

        return true;

    }

    final static public function validateExpression($expression) {

        try {

            $cron = CronExpression::factory($expression);

            $s = $cron->getNextRunDate()->format('c');

            $e = $cron->getExpression();

        }
        catch (Exception $e) {

            throw $e;

            // throw new Exception("Invalid cron expression");

        }

        return array( $s, $e );

    }

    static private function getJobs() {
        
        $jobs = Cache::get();

        if ( $jobs !== false ) return $jobs;

        try{

            $db = new EnhancedDatabase(
                EXTENDER_DATABASE_MODEL,
                EXTENDER_DATABASE_HOST,
                EXTENDER_DATABASE_PORT,
                EXTENDER_DATABASE_NAME,
                EXTENDER_DATABASE_USER,
                EXTENDER_DATABASE_PASS
            );

            $result = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)
                ->table(EXTENDER_DATABASE_TABLE_JOBS)
                ->keys(array("id","name","task","description",
                    "min","hour","dayofmonth","month","dayofweek","year",
                    "params","lastrun"))
                ->where("enabled","=",true)
                ->get();

        }
        catch (Exception $e) {

            unset($db);

            throw $e;

        }
        
        unset($db);

        $jobs = $result['data'];

        Cache::set($jobs);

        return $jobs;
        
    }
    
    static private function shouldRunJob($job, $logger, $timestamp) {

        $expression = implode(" ",Array($job['min'],$job['hour'],$job['dayofmonth'],$job['month'],$job['dayofweek'],$job['year'])); 
        
        $last_date = date_create();

        date_timestamp_set($last_date, $job['lastrun']);

        try {

            $cron = CronExpression::factory($expression);

            $next_calculated_run = $cron->getNextRunDate($last_date)->format('U');

        }
        catch (Exception $e) {

            $logger->error("Job ".$job['name']." cannot be executed due to cron parsing error",array(
                "ERROR" => $e->getMessage(),
                "ERRID" => $e->getCode()
            ));

            return false;

        }

        $torun = $next_calculated_run <= $timestamp ? true : false;

        $logger->debug("Job ".$job['name'].($torun ? " will be" : " will not be")." executed", array(
            "EXPRESSION" => $expression,
            "LASTRUNDATE"=> date('c',$job['lastrun']),
            "NEXTRUN"    => date('c',$next_calculated_run)
        ));

        return $torun;

    }

}
