<?php namespace Comodojo\Extender;

use \Console_Color2;
use \Console_Table;
use \Comodojo\Extender\Scheduler\Scheduler;
use \Comodojo\Extender\Scheduler\Schedule;
use \Comodojo\Extender\Runner\JobsRunner;
use \Comodojo\Extender\Runner\JobsResult;
use \Comodojo\Extender\Job\Job;
use \Comodojo\Extender\Debug;
use \Comodojo\Extender\TasksTable;
use \Exception;

/**
 * Extender main class
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

class Extender {

    // configurable things

    /**
     * Max result lenght (in bytes) retrieved from parent in miltithread mode
     *
     * @var int
     */
    private $max_result_bytes_in_multithread = null;

    /**
     * Maximum time (in seconds) the parent will wait for child tasks to be completed (in miltithread mode)
     *
     * @var int
     */
    private $max_childs_runtime = null;

    /**
     * Multithread mode
     *
     * @var bool
     */
    private $multithread_mode = false;

    /**
     * Verbose mode, if requested via command line arg -v
     *
     * @var bool
     */
    private $verbose_mode = false;

    /**
     * Summary mode, if requested via command line arg -s
     *
     * @var bool
     */
    private $summary_mode = false;

    /**
     * Daemon mode, if requested via command line arg -d
     *
     * @var bool
     */
    private $daemon_mode = false;   

    /**
     * Timestamp, relative, of current extend() cycle
     *
     * @var float
     */
    private $timestamp = null;

    /**
     * Timestamp, absolute, sinnce extender was initiated
     *
     * @var float
     */
    private $timestamp_absolute = null;

    /**
     * PID of the parent extender process
     *
     * @var int
     */
    private $parent_pid = null;

    /**
     * Set exender in paused mode (no job will be processed)
     *
     * @var bool
     */
    private $paused = false;

    // Helper classes

    /**
     * Events manager instance
     *
     * @var Object
     */
    private $events = null;

    /**
     * Console_Color2 instance
     *
     * @var Object
     */
    private $color = null;

    /**
     * Logger instance
     *
     * @var Object
     */
    private $logger = null;

    /**
     * JobsRunner instance
     *
     * @var Object
     */
    private $runner = null;

    /**
     * TasksTable instance
     *
     * @var Object
     */
    private $tasks = null;

    /**
     * JobsResult instance
     *
     * @var Object
     */
    private $results = null;

    // checks and locks are static!
    
    // local archives

    /**
     * Failed processes, refreshed each cycle (in daemon mode)
     *
     * @var int
     */
    private $failed_processes = 0;
    
    /**
     * Completed processes
     *
     * @var int
     */
    private $completed_processes = 0;

    /**
     * Constructor method
     *
     * Prepare extender environment, do checks and fire extender.ready event
     */
    final public function __construct() {

        // setup default timezone (in daemon mode, timezone warning may break extender)

        date_default_timezone_set(defined('EXTENDER_TIMEZONE') ? EXTENDER_TIMEZONE : 'Europe/Rome');

        $this->timestamp_absolute = microtime(true);

        $this->color = new Console_Color2();

        // get command line options (vsdh)

        list($this->verbose_mode, $this->summary_mode, $this->daemon_mode, $help_mode) = self::getCommandlineOptions();

        if ( $help_mode ) {

            self::showHelp($this->color);

            exit(0);

        }

        $this->logger = new Debug($this->verbose_mode, $this->color);

        $this->events = new Events($this->logger);

        // do checks

        $check_constants = Checks::constants();

        if ( $check_constants !== true ) {

            $this->logger->critical($check_constants);

            exit(1);

        }

        if ( Checks::cli() === false ) {

            $this->logger->critical("Extender runs only in php-cli, exiting");

            exit(1);

        }

        if ( Checks::database() === false ) {

            $this->logger->critical("Extender database not reachable, exiting");

            exit(1);

        }

        $this->tasks = new TasksTable();

        $this->schedule = new Schedule();

        // setup extender parameters

        $this->max_result_bytes_in_multithread = defined('EXTENDER_MAX_RESULT_BYTES') ? filter_var(EXTENDER_MAX_RESULT_BYTES, FILTER_VALIDATE_INT) : 2048;

        $this->max_childs_runtime = defined('EXTENDER_MAX_CHILDS_RUNTIME') ? filter_var(EXTENDER_MAX_CHILDS_RUNTIME, FILTER_VALIDATE_INT) : 300;

        $this->multithread_mode = defined('EXTENDER_MULTITHREAD_ENABLED') ? filter_var(EXTENDER_MULTITHREAD_ENABLED, FILTER_VALIDATE_BOOLEAN) : false;

        // if in daemon mode, remember parent pid, setup lock and register signal handlers

        if ( $this->daemon_mode ) {

            $this->parent_pid = posix_getpid();

            Lock::register($this->parent_pid);

            $this->adjustNiceness();

            if ( Checks::signals() ) $this->registerSignals();

        }

        // init the runner

        $this->runner = new JobsRunner($this->logger, $this->getMultithreadMode(), $this->max_result_bytes_in_multithread, $this->max_childs_runtime);

        $this->logger->notice("Extender ready");

        // fire extender ready event

        $this->events->fire("extender.ready", "VOID", $this->logger);

        // we are ready to go!

    }

    /**
     * Set max result length (in bytes) that should be read from child tasks
     *
     * @param   int     $bytes  Maximum length (bytes)
     *
     * @return  Object          $this
     */
    final public function setMaxResultLength($bytes) {

        $this->max_result_bytes_in_multithread = filter_var($bytes, FILTER_VALIDATE_INT, array( "default" => 2048 ));

        return $this;

    }

    /**
     * Get max result length (in bytes)
     *
     * @return  int     Bytes parent should read (max)
     */
    final public function getMaxResultLength() {

        return $this->max_result_bytes_in_multithread;

    }

    /**
     * Set maximum time (in seconds) the parent will wait for child tasks to be completed (in miltithread mode)
     *
     * After $time seconds, parent will start killing tasks
     *
     * @param   int     $time   Maximum time (seconds)
     *
     * @return  Object          $this
     */
    final public function setMaxChildsRuntime($time) {

        $this->max_childs_runtime = filter_var($time, FILTER_VALIDATE_INT, array( "min_range" => 1, "default" => 300 ));

        return $this;

    }

    /**
     * Get maximum time (in seconds) the parent will wait for child tasks to be completed (in miltithread mode)
     *
     * @return  int     Time parent will wait for childs to be completed
     */
    final public function getMaxChildsRuntime() {

        return $this->max_childs_runtime;

    }

    /**
     * Set working mode (single or multithread)
     *
     * If multithread enabled, extender will use pcntl to fork child tasks
     *
     * @param   bool    $mode   Enable/disable multithread
     *
     * @return  Object          $this
     */
    final public function setMultithreadMode($mode) {

        $this->multithread_mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Get multithread mode status
     *
     * @return  bool    True if enabled, false if disabled
     */
    final public function getMultithreadMode() {

        return ( $this->multithread_mode AND Checks::multithread() ) ? true : false;

    }

    /**
     * Get daemon mode status
     *
     * @return  bool    True if enabled, false if disabled
     */
    final public function getDaemonMode() {

        return $this->daemon_mode;

    }

    /**
     * Register a task to TasksTable
     *
     * @param   string    $name         Task name (unique)
     * @param   string    $target       Target task file
     * @param   string    $description  A brief description for the task
     * @param   string    $class        (optional) Task class, if different from file name
     * @param   bool      $relative     (optional) If relative, a task will be loaded in EXTENDER_TASK_FOLDER
     *
     * @return  bool
     */
    final public function addTask($name, $target, $description, $class=null, $relative=true) {

        if ( $this->tasks->addTask($name, $target, $description, $class, $relative) === false ) {

            $this->logger->warning("Skipping task due to invalid definition", array(
                "NAME"       => $name,
                "TARGET"     => $target,
                "DESCRIPTION"=> $description,
                "CLASS"      => $class,
                "RELATIVE"   => $relative
            ));

            return false;

        }

        else return true;

    }

    /**
     * Include a plugin
     *
     * @param   string  $plugin     The plugin name
     * @param   string  $folder     (optional) plugin folder (if omitted, dispatcher will use default one)
     */
    final public function loadPlugin($plugin, $folder=EXTENDER_PLUGIN_FOLDER) {

        include $folder.$plugin.".php";

    }

    /**
     * Do extend!
     *
     */
    public function extend() {

        // dispatch signals (if multithread active)

        if ( $this->getMultithreadMode() ) pcntl_signal_dispatch();

        // if extender is paused (SIGINT), skip to extend

        if ( $this->paused ) return;

        // fix relative timestamp

        $this->timestamp = microtime(true);

        // fire tasktable event

        $this->tasks = $this->events->fire("extender.tasks", "TASKSTABLE", $this->tasks);

        try {

            // get schedules and dispatch schedule event

            $schedules = Scheduler::getSchedules($this->logger, $this->timestamp);
        
            $this->schedule->setSchedules( $schedules );

            $this->schedule = $this->events->fire("extender.schedule", "SCHEDULE", $this->schedule);

            // if no jobs in queue, exit gracefully

            if ( $this->schedule->howMany() == 0 ) {

                $this->logger->info("No jobs to process right now, exiting");

                $this->logger->notice("Extender completed\n");

                if ( $this->getDaemonMode() === false ) exit(0);

                return;

            }

            // compose jobs

            foreach ($this->schedule->getSchedules() as $schedule) {

                if ( $this->tasks->isTaskRegistered($schedule['task']) ) {

                    $job = new Job();

                    $job->setName( $schedule['name'] )
                        ->setId( $schedule['id'] )
                        ->setParameters( unserialize($schedule['params']) )
                        ->setTask( $schedule['task'] )
                        ->setTarget( $this->tasks->getTarget($schedule['task']) )
                        ->setClass( $this->tasks->getClass($schedule['task']) );

                    $this->runner->addJob($job);

                } else {

                    $this->logger->warning("Skipping job due to unknown task", array(
                        "ID"     => $schedule['id'],
                        "NAME"   => $schedule['name'],
                        "TASK"   => $schedule['task']
                    ));

                }

            }

            // lauch runner

            $result = $this->runner->run();

            // free runner for next cycle

            $this->runner->free();

            // compose results

            $this->results = new JobsResult($result);

            // update schedules

            Scheduler::updateSchedules($this->logger, $result);

            // increment counters

            foreach ($result as $r) {
                
                if ( $r[2] ) $this->completed_processes++;

                else $this->failed_processes++;

            }

        } catch (Exception $e) {

            $this->logger->error($e->getMessage());

            if ( $this->getDaemonMode() === false ) exit(1);
            
        }

        // fire result event

        $this->events->fire("extender.result", "VOID", $this->results);

        $this->logger->notice("Extender completed\n");

        // show summary (if -s)

        if ( $this->summary_mode ) self::showSummary($this->timestamp, $result, $this->color);

        if ( $this->getDaemonMode() === false ) exit(0);

    }

    /**
     * Change parent process priority according to EXTENDER_NICENESS
     *
     */
    final public function adjustNiceness() {

        if ( defined("EXTENDER_PARENT_NICENESS") ) {

            $niceness = proc_nice(EXTENDER_PARENT_NICENESS);

            if ( $niceness == false ) $this->logger->warning("Unable to set parent process niceness to ".EXTENDER_PARENT_NICENESS);

        }

    }

    /**
     * Register signals
     *
     */
    final public function registerSignals() {

        $pluggable_signals = array(
            SIGHUP, SIGCHLD, SIGUSR2, SIGILL, SIGTRAP, SIGABRT, SIGIOT, SIGBUS, SIGFPE,
            SIGSEGV, SIGPIPE, SIGALRM, SIGTTIN, SIGTTOU, SIGURG, SIGXCPU, SIGXFSZ,
            SIGVTALRM, SIGPROF, SIGWINCH, SIGIO, SIGSYS, SIGBABY
        );

        if (defined('SIGPOLL'))   $pluggable_signals[] = SIGPOLL;
        if (defined('SIGPWR'))    $pluggable_signals[] = SIGPWR;
        if (defined('SIGSTKFLT')) $pluggable_signals[] = SIGSTKFLT;

        // register supported signals

        pcntl_signal(SIGTERM, array($this,'sigTermHandler'));

        pcntl_signal(SIGINT, array($this,'sigTermHandler'));

        pcntl_signal(SIGTSTP, array($this,'sigStopHandler'));

        pcntl_signal(SIGCONT, array($this,'sigContHandler'));

        pcntl_signal(SIGUSR1, array($this,'sigUsr1Handler'));

        // register pluggable signals

        foreach ($pluggable_signals as $signal) {
            
            pcntl_signal($signal, array($this,'genericSignalHandler'));

        }

        // register shutdown function

        register_shutdown_function(array($this,'shutdown'));

    }

    /**
     * Delete $pid file after exit() called
     *
     */
    final public function shutdown() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Shutdown in progress, cleaning environment.");

            Lock::release();

            Status::release();

        }

    }

    final public function sigTermHandler() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received TERM signal, shoutting down extender gracefully");

            $this->runner->killAll($this->parent_pid);

            exit(1);

        }

    }

    final public function sigUsr1Handler() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received USR1 signal, dumping status parameters");

            Status::dump($this->timestamp_absolute, $this->parent_pid, $this->completed_processes, $this->failed_processes);

        }

    }

    final public function sigStopHandler() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received STOP signal, pausing extender");

            $this->paused = true;

        }

    }

    final public function sigContHandler() {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received CONT signal, resuming extender");

            $this->paused = false;

        }

    }

    final public function genericSignalHandler($signal) {

        if ( $this->parent_pid == posix_getpid() ) {

            $this->logger->info("Received ".$signal." signal, firing associated event(s)");

            $this->events->fire("extender.signal.".$signal, "VOID", $this);

        }

    }

    private static function showHelp($color) {

        echo Version::getDescription();

        echo "\nVersion: ".$color->convert("%g".Version::getVersion()."%n");

        echo "\n\nAvailable options:";

        echo "\n------------------";

        echo "\n".$color->convert("%g -v %n").": verbose mode, extender will print debug information (use it with daemon mode for testing purpose only!)";

        echo "\n".$color->convert("%g -s %n").": show summary of executed jobs (if any)";

        echo "\n".$color->convert("%g -d %n").": run extender in daemon mode";

        echo "\n".$color->convert("%g -h %n").": show this help";

        echo "\n\n";

    }

    private static function getCommandlineOptions() {

        $options = getopt("svdh");

        return array(
            array_key_exists('v', $options) ? true : false,
            array_key_exists('s', $options) ? true : false,
            array_key_exists('d', $options) ? true : false,
            array_key_exists('h', $options) ? true : false
        );

    }

    private static function showSummary($timestamp, $completed_processes, $color) {

        $header_string = "\n\n --- Comodojo Extender Summary --- ".date('c',$timestamp)."\n\n";

        $tbl = new Console_Table(CONSOLE_TABLE_ALIGN_LEFT, CONSOLE_TABLE_BORDER_ASCII, 1, null, true);

        $tbl->setHeaders(array(
            'Pid',
            'Name',
            'Success',
            'Result (truncated)',
            'Time elapsed'
        ));
        
        foreach ($completed_processes as $key => $completed_process) {

            $pid = $completed_process[0];

            $name = $completed_process[1];

            $success = $color->convert($completed_process[2] ? "%gYES%n" : "%rNO%n");

            $result = str_replace(array("\r", "\n"), " ", $completed_process[5]);

            $result = strlen($result) >= 80 ? substr($result,0,80)."..." : $result;

            $elapsed = $completed_process[2] ? ($completed_process[4]-$completed_process[3]) : "--";

            $tbl->addRow(array(
                $pid,
                $name,
                $success,
                $result,
                $elapsed
            ));

        }

        $footer_string = "\n\nTotal script runtime: ".(microtime(true)-$timestamp)." seconds\r\n\n";
        
        print $header_string.$tbl->getTable().$footer_string;
        
    }

}
