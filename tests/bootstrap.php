<?php

// Simple bootloader for phpunit using composer autoloader

$loader = require __DIR__ . "/../vendor/autoload.php";

use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Database\QueryBuilder\Column;
use \Comodojo\Exception\DatabaseException;

// Standard framework configuration
define("EXTENDER_TIMEZONE", "Europe/Rome");
define("EXTENDER_REAL_PATH", realpath(dirname(__FILE__))."/resources/");
define("EXTENDER_MULTITHREAD_ENABLED", true);
define("EXTENDER_IDLE_TIME", 1);
define("EXTENDER_MAX_RESULT_BYTES", 2048);
define("EXTENDER_MAX_CHILDS", 0);
define("EXTENDER_MAX_CHILDS_RUNTIME", 600);
// define("EXTENDER_PARENT_NICENESS", 0);
// define("EXTENDER_CHILD_NICENESS", 0);
define("EXTENDER_LOG_ENABLED", false);
define("EXTENDER_LOG_NAME", "extender");
define("EXTENDER_LOG_TARGET", "extender.log");
define("EXTENDER_LOG_LEVEL", "ERROR");
define("EXTENDER_LOG_FOLDER", EXTENDER_REAL_PATH."logs/");
define("EXTENDER_DATABASE_FOLDER", EXTENDER_REAL_PATH."database/");
define("EXTENDER_CACHE_FOLDER", EXTENDER_REAL_PATH."cache/");
define("EXTENDER_DATABASE_MODEL", "SQLITE_PDO");
define("EXTENDER_DATABASE_HOST", "localhost");
define("EXTENDER_DATABASE_PORT",1);
define("EXTENDER_DATABASE_NAME", EXTENDER_DATABASE_FOLDER."extender.sqlite");
define("EXTENDER_DATABASE_USER", "comodojo");
define("EXTENDER_DATABASE_PASS", "");
define("EXTENDER_DATABASE_PREFIX", "extender_");
define("EXTENDER_DATABASE_TABLE_JOBS", "jobs");
define("EXTENDER_DATABASE_TABLE_WORKLOGS", "worklogs");

// custom config files
define("EXTENDER_TASKS_CONFIG", EXTENDER_REAL_PATH."tasks.yaml");
define("EXTENDER_COMMANDS_CONFIG", EXTENDER_REAL_PATH."commands.yaml");
define("EXTENDER_PLUGINS_CONFIG", EXTENDER_REAL_PATH."plugins.yaml");

// override exit codes
define("COMODOJO_PHPUNIT_TEST", true);

// create database
try {

    $db = new EnhancedDatabase(
        EXTENDER_DATABASE_MODEL,
        EXTENDER_DATABASE_HOST,
        EXTENDER_DATABASE_PORT,
        EXTENDER_DATABASE_NAME,
        EXTENDER_DATABASE_USER,
        EXTENDER_DATABASE_PASS
    );

    $db->autoClean();

    $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)->drop(true);

    $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_WORKLOGS)->drop(true);

    $jobs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_JOBS)
       ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
       ->column(Column::create('name','STRING')->length(128)->notNull()->unique())
       ->column(Column::create('task','STRING')->length(128)->notNull())
       ->column(Column::create('description','TEXT')->defaultValue(null))
       ->column(Column::create('enabled','BOOL')->defaultValue(0))
       ->column(Column::create('min','STRING')->length(16)->defaultValue(null))
       ->column(Column::create('hour','STRING')->length(16)->defaultValue(null))
       ->column(Column::create('dayofmonth','STRING')->length(16)->defaultValue(null))
       ->column(Column::create('month','STRING')->length(16)->defaultValue(null))
       ->column(Column::create('dayofweek','STRING')->length(16)->defaultValue(null))
       ->column(Column::create('year','STRING')->length(16)->defaultValue(null))
       ->column(Column::create('params','TEXT')->defaultValue(null))
       ->column(Column::create('lastrun','INTEGER')->length(64)->defaultValue(null))
       ->column(Column::create('firstrun','INTEGER')->length(64)->notNull())
       ->create(EXTENDER_DATABASE_TABLE_JOBS);

    // $db->clean();

    $worklogs = $db->tablePrefix(EXTENDER_DATABASE_PREFIX)->table(EXTENDER_DATABASE_TABLE_WORKLOGS)
       ->column(Column::create('id','INTEGER')->unsigned()->autoIncrement()->primaryKey())
       ->column(Column::create('pid','INTEGER')->unsigned()->defaultValue(null))
       ->column(Column::create('jobid','INTEGER')->unsigned()->defaultValue(null))
       ->column(Column::create('name','STRING')->length(128)->notNull())
       ->column(Column::create('task','STRING')->length(128)->notNull())
       ->column(Column::create('status','STRING')->length(12)->notNull())
       ->column(Column::create('success','BOOL')->defaultValue(0))
       ->column(Column::create('result','TEXT')->defaultValue(null))
       ->column(Column::create('start','STRING')->length(64)->notNull())
       ->column(Column::create('end','STRING')->length(64)->defaultValue(null))
       ->create(EXTENDER_DATABASE_TABLE_WORKLOGS);

} catch (DatabaseException $de) {

    unset($db);

    throw new ShellException("Database error: ".$de->getMessage());

}

unset($db);
