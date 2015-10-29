<?php namespace Comodojo\Extender\Scheduler;

use \Cron\CronExpression;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Extender\Cache;
use \Comodojo\Extender\Planner;
use \Exception;

/**
 * Extender scheduler
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

class Scheduler {

    /**
     * Get planned schedules
     *
     * @param   \Comodojo\Extender\Debug   $logger
     * @param   float                      $timestamp
     *
     * @return  array
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function getSchedules($logger, $timestamp) {

        $schedules = array();

        $planned = array();

        try {
            
            $jobs = self::getJobs();

            foreach ($jobs as $job) {

                if ( self::shouldRunJob($job, $logger, $timestamp) ) array_push($schedules, $job);

                else $planned[] = self::shouldPlanJob($job);
                
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

        return array( $schedules, empty($planned) ? null : min($planned) );

    }

    /**
     * Update schedules (last run)
     *
     * @param   Object  $logger
     * @param   array   $completed_processes
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    final public static function updateSchedules($logger, $completed_processes) {

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

                $db->table(EXTENDER_DATABASE_TABLE_JOBS)
                    ->keys("lastrun")
                    ->values($process[3])
                    ->where('id','=',$process[6])
                    ->update();

            }

        } catch (DatabaseException $de) {

            unset($db);

            throw $de;

        }

        unset($db);

        Cache::purge();
        
        Planner::release();

    }

    /**
     * Get a schedule by name
     *
     * @param   string  $name
     *
     * @return  array|null
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function getSchedule($name) {

        if ( empty($name) ) throw new Exception("Invalid job name");

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
                ->keys(array("id","task", "description",
                    "min", "hour", "dayofmonth", "month",
                    "dayofweek", "year", "params","firstrun", "lastrun"))
                ->where("name","=",$name)
                ->get();

        } catch (DatabaseException $e) {

            throw $e;

        }

        if ( $result->getLength() == 0 ) return null;

        $data = $result->getData();

        $expression = implode(" ",array($data[0]['min'],$data[0]['hour'],$data[0]['dayofmonth'],$data[0]['month'],$data[0]['dayofweek'],$data[0]['year']));

        return array(
            "id" => $data[0]["id"],
            "name" => $name,
            "task" => $data[0]["task"],
            "description" => $data[0]["description"],
            "expression" => $expression,
            "params" => unserialize($data[0]["params"]),
            "firstrun" => $data[0]["firstrun"],
            "lastrun" => $data[0]["lastrun"],
            "nextrun" => self::shouldPlanJob($data[0])
        );

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
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function addSchedule($expression, $name, $task, $description=null, $params=array()) {

        if ( empty($name) ) throw new Exception("Invalid job name");

        if ( empty($task) ) throw new Exception("A job feels alone without a task");

        try {
            
            list( $next_calculated_run, $parsed_expression ) = self::validateExpression($expression);

            $firstrun = (int)date("U", strtotime($next_calculated_run));

            list($min, $hour, $dayofmonth, $month, $dayofweek, $year) = $parsed_expression;

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
                    "dayofweek", "year", "params","firstrun"))
                ->values(array($name, $task, $description,
                    $min, $hour, $dayofmonth, $month,
                    $dayofweek, $year, $parameters, $firstrun))
                ->store();

        } catch (DatabaseException $e) {
            
            throw $de;

        } catch (Exception $e) {
            
            throw $e;

        }

        Cache::purge();
        
        Planner::release();

        return array($result->getInsertId(), $next_calculated_run);

    }

    /**
     * Remove a schedule
     *
     * @param   string  $name
     *
     * @return  bool
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function removeSchedule($name) {
        
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

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        if ( $result->getAffectedRows() == 0 ) return false;

        Cache::purge();
        
        Planner::release();

        return true;

    }

    /**
     * Update single schedule (last run)
     *
     * @param   string  $name
     * @param   float   $lastrun
     *
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function updateSchedule($name, $lastrun) {

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
        
        Planner::release();

    }

    /**
     * Enable a schedule
     *
     * @param   string  $name
     *
     * @return  bool
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function enableSchedule($name) {

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
                ->values(array(true))
                ->where("name","=",$name)
                ->update();

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        Cache::purge();
        
        Planner::release();

        return $result->getAffectedRows() == 1 ? true : false;

    }

    /**
     * Disable a schedule
     *
     * @param   string  $name
     *
     * @return  bool
     * @throws  \Comodojo\Exception\DatabaseException
     * @throws  \Exception
     */
    final public static function disableSchedule($name) {

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
                ->values(array(false))
                ->where("name","=",$name)
                ->update();

        } catch (DatabaseException $de) {
            
            throw $de;

        }

        Cache::purge();
        
        Planner::release();

        return $result->getAffectedRows() == 1 ? true : false;

    }

    /**
     * Validate a cron expression and, if valid, return next run timestamp plus
     * an array of expression parts
     *
     * @param   string  $expression
     *
     * @return  array   Next run timestamp at first position, expression parts at second
     * @throws  \Exception
     */
    final public static function validateExpression($expression) {

        try {

            $cron = CronExpression::factory($expression);

            $s = $cron->getNextRunDate()->format('c');

            $e = $cron->getExpression();

            $e_array = preg_split('/\s/', $e, -1, PREG_SPLIT_NO_EMPTY);

            $e_count = count($e_array);

            if ( $e_count < 5 || $e_count > 6 ) throw new Exception($e." is not a valid cron expression");

            if ( $e_count == 5 ) $e_array[] = "*";

        }
        catch (Exception $e) {

            throw $e;

        }

        return array( $s, $e_array );

    }

    /**
     * Get planned jobs
     *
     * @return  array
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function getJobs() {
        
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
                    "params","lastrun","firstrun"))
                ->where("enabled","=",true)
                ->get();

        }
        catch (DatabaseException $de) {

            unset($db);

            throw $de;

        }
        
        unset($db);

        $jobs = $result->getData();

        Cache::set($jobs);

        return $jobs;
        
    }
    
    /**
     * Determine if a job should be executed
     *
     * @param   array   $job
     * @param   object  $logger
     * @param   float   $timestamp
     *
     * @return  bool
     * @throws  \Exception
     */
    private static function shouldRunJob($job, $logger, $timestamp) {

        $expression = implode(" ",array($job['min'],$job['hour'],$job['dayofmonth'],$job['month'],$job['dayofweek'],$job['year'])); 

        if ( empty($job['lastrun']) ) {

            $next_calculated_run = (int)$job['firstrun'];

        } else {

            $last_date = date_create();

            date_timestamp_set($last_date, (int)$job['lastrun']);    

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

        }

        $torun = $next_calculated_run <= $timestamp ? true : false;
        
        $logger->debug("Job ".$job['name'].($torun ? " will be" : " will not be")." executed", array(
            "EXPRESSION"  => $expression,
            "FIRSTRUNDATE"=> date('c',$job['firstrun']),
            "LASTRUNDATE" => date('c',$job['lastrun']),
            "NEXTRUN"     => date('c',$next_calculated_run)
        ));

        return $torun;

    }

    /**
     * Determine next planned jobs
     *
     * @param   array   $job
     *
     * @return  int
     */
    private static function shouldPlanJob($job) {

        $expression = implode(" ",array($job['min'],$job['hour'],$job['dayofmonth'],$job['month'],$job['dayofweek'],$job['year']));

        if ( empty($job['lastrun']) ) {

            $next_calculated_run = (int)$job['firstrun'];

        } else {

            $last_date = date_create();

            date_timestamp_set($last_date, (int)$job['lastrun']);  

            try {

                $cron = CronExpression::factory($expression);

                $next_calculated_run = $cron->getNextRunDate($last_date)->format('U');

            }
            catch (Exception $e) {

                return false;

            }

        }

        return $next_calculated_run;

    }

}
