<?php namespace Comodojo\Extender;

use \Console_Color2;
use \Console_Table;
use \Comodojo\Extender\Scheduler\Scheduler;
use \Comodojo\Extender\Job\JobsRunner;
use \Comodojo\Extender\Job\Job;
use \Comodojo\Extender\Debug;
use \Exception;

class Extender {

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
	 * Timestamp of current execution cycle
	 *
	 * @var float
	 */
	private $timestamp = null;

	private $events = null;

	private $color = null;

	private $logger = null;

	private $tasks = array();
	
	private $running_processes = array();
	
	private $completed_processes = array();
	
	private $ipc_array = array();

	final public function __construct() {

		date_default_timezone_set(defined('EXTENDER_TIMEZONE') ? EXTENDER_TIMEZONE : 'Europe/Rome');

		$this->timestamp = microtime(true);

		$this->events = new Events();

		$this->color = new Console_Color2();

		$check_constants = self::checkConstants();

		if ( $check_constants !== true ) {

			self::showError($check_constants, $this->color);

			exit(1);

		}

		if ( self::extenderIsRunningFromCli() === false ) {

			self::showError("Extender runs only in php-cli, exiting", $this->color);

			exit(1);

		}

		list($this->verbose_mode, $this->summary_mode) = self::getCommandlineOptions();

		$this->logger = new Debug($this->verbose_mode, $this->color);

		$this->events = new Events($this->logger);

		$this->max_result_bytes_in_multithread = defined('EXTENDER_MAX_RESULT_BYTES') ? filter_var(EXTENDER_MAX_RESULT_BYTES, FILTER_VALIDATE_INT) : 2048;

		$this->max_childs_runtime = defined('EXTENDER_MAX_CHILDS_RUNTIME') ? filter_var(EXTENDER_MAX_CHILDS_RUNTIME, FILTER_VALIDATE_INT) : 300;

		$this->multithread_mode = defined('EXTENDER_MULTITHREAD_ENABLED') ? filter_var(EXTENDER_MULTITHREAD_ENABLED, FILTER_VALIDATE_BOOLEAN) : false;

		$this->logger->notice("Extender ready");

	}

	/**
	 * Set max result length (in bytes) that should be read from child tasks
	 *
	 * @param 	int 	$bytes 	Maximum length (bytes)
	 *
	 * @return 	Object 			$this
	 */
	final public function setMaxResultLength($bytes) {

		$this->max_result_bytes_in_multithread = filter_var($bytes, FILTER_VALIDATE_INT, array( "default" => 2048 ));

		return $this;

	}

	/**
	 * Get max result length (in bytes)
	 *
	 * @return 	int 	Bytes parent should read (max)
	 */
	final public function getMaxResultLength() {

		return $this->max_result_bytes_in_multithread;

	}

	/**
	 * Set maximum time (in seconds) the parent will wait for child tasks to be completed (in miltithread mode)
	 *
	 * After $time seconds, parent will start killing tasks
	 *
	 * @param 	int 	$time 	Maximum time (seconds)
	 *
	 * @return 	Object 			$this
	 */
	final public function setMaxChildsRuntime($time) {

		$this->max_childs_runtime = filter_var($time, FILTER_VALIDATE_INT, array( "min_range" => 1, "default" => 300 ));

		return $this;

	}

	/**
	 * Get maximum time (in seconds) the parent will wait for child tasks to be completed (in miltithread mode)
	 *
	 * @return 	int 	Time parent will wait for childs to be completed
	 */
	final public function getMaxChildsRuntime() {

		return $this->max_childs_runtime;

	}

	/**
	 * Set working mode (single or multithread)
	 *
	 * If multithread enabled, extender will use pcntl to fork child tasks
	 *
	 * @param 	bool 	$mode 	Enable/disable multithread
	 *
	 * @return 	Object 			$this
	 */
	final public function setMultithreadMode($mode) {

		$this->multithread_mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

		return $this;

	}

	/**
	 * Get multithread mode status
	 *
	 * @return 	bool 	True if enabled, false if disabled
	 */
	final public function getMultithreadMode() {

		return ( $this->multithread_mode AND self::isMultithreadPossible() ) ? true : false;

	}

	final public function addTask($name, $target, $description, $class=null, $relative=true) {

		if ( empty($name) OR empty($target) ) {

			$this->logger->warning("Skipping task due to invalid definition", array(
				"NAME"		 =>	$name,
				"TARGET"	 =>	$target,
				"DESCRIPTION"=> $description,
				"CLASS"      => $class,
				"RELATIVE"	 => $relative
			));

			return false;

		}

		$this->tasks[$name] = array(
			"description" => $description,
			"target"	  => $relative ? EXTENDER_TASK_FOLDER.$target : $target,
			"class"		  => empty($class) ? preg_replace('/\\.[^.\\s]{3,4}$/', '', $target) : $class
		);

		return true;

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

	public function extend() {

		try {
		
			$schedules = Scheduler::getSchedules($this->logger, $this->timestamp);

			if ( empty($schedules) ) {

				$this->logger->info("No jobs to process right now, exiting");

				$this->logger->notice("Extender completed\n");

				exit(0);

			}

			$runner = new JobsRunner($this->logger, $this->max_result_bytes_in_multithread, $this->max_childs_runtime);

			foreach ($schedules as $schedule) {

				if ( array_key_exists($schedule['task'], $this->tasks) ) {

					$job = new Job();

					$job->setName($schedule['name'])
						->setId($schedule['id'])
						->setParameters($schedule['parameters'])
						->setTask($schedule['task'])
						->setTarget($this->tasks[$schedule['task']]["target"])
						->setClass($this->tasks[$schedule['task']]["class"]);

					$runner->addJob($job);

				} else {

					$this->logger->warning("Skipping job due to unknown task", array(
						"ID"	 =>	$schedule['id'],
						"NAME"	 =>	$schedule['name'],
						"TASK"   => $schedule['task']
					));

				}

			}

			$result = $runner->run();

			Scheduler::updateSchedules($this->logger, $result);

		} catch (Exception $e) {

			self::showError($e->getMessage(), $this->color);

			exit(1);
			
		}

		$this->logger->notice("Extender completed\n");

		if ( $this->summary_mode ) self::showSummary($this->timestamp, $result, $this->color);

		exit(0);

	}

	private static function extenderIsRunningFromCli() {

		return php_sapi_name() === 'cli';

	}

	private static function getCommandlineOptions() {

		$options = getopt("sv");

		return array(
			array_key_exists('v', $options) ? true : false,
			array_key_exists('s', $options) ? true : false
		);

	}

	private static function isMultithreadPossible() {

		return function_exists("pcntl_fork");

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

		$footer_string .= "\n\nTotal script runtime: ".(microtime(true)-$timestamp)." seconds\r\n";
		
		print $header_string.$tbl->getTable().$footer_string;
		
	}

	private static function showError($error, $color) {

		print $color->convert("%rExtender died due to fatal error%n\n");

		print $color->convert("%r".$error."%n\n");

	}

	private static function checkConstants() {

		if ( !defined("EXTENDER_DATABASE_MODEL") ) return "Invalid database model";
		if ( !defined("EXTENDER_DATABASE_HOST") ) return "Unknown database host";
		if ( !defined("EXTENDER_DATABASE_PORT") ) return "Invalid database port";
		if ( !defined("EXTENDER_DATABASE_NAME") ) return "Invalid database name";
		if ( !defined("EXTENDER_DATABASE_USER") ) return "Invalid database user";
		if ( !defined("EXTENDER_DATABASE_PASS") ) return "Invalid database password";
		if ( !defined("EXTENDER_DATABASE_PREFIX") ) return "Invalid database table prefix";
		if ( !defined("EXTENDER_DATABASE_TABLE_JOBS") ) return "Invalid database jobs' table prefix";
		if ( !defined("EXTENDER_DATABASE_TABLE_WORKLOGS") ) return "Invalid database worklogs' table";

		return true;

	}

}
