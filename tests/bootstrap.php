<?php

// use \Doctrine\DBAL\Configuration;
// use \Doctrine\DBAL\DriverManager;
// use \Doctrine\DBAL\Schema\Schema;

// init the autoloader
$loader = require __DIR__ . "/../vendor/autoload.php";

// add PSR4 test classes
$loader->addPsr4('Comodojo\\Extender\\Tests\\', __DIR__."/Extender");
/*
$database = __DIR__.'/resources/database/extender.sqlite';

// delete the old database (if any)
if ( file_exists($database) ) unlink($database);

// create the dummy sqlite database
$config = new Configuration();
$connectionParams = array(
    'dbname' => 'sqlite',
    'driver' => 'pdo_sqlite',
    'path' => $database
);
$conn = DriverManager::getConnection($connectionParams, $config);
$platform = $conn->getDatabasePlatform();

// remove old tables (if any)
// $manager = $conn->getSchemaManager();
// if ($manager->tablesExist(array('worklogs')) == true) {
//       $manager->dropTable('worklogs');
// }
// if ($manager->tablesExist(array('jobs')) == true) {
//       $manager->dropTable('jobs');
// }
// if ($manager->tablesExist(array('queue')) == true) {
//       $manager->dropTable('queue');
// }

// define new tables
$schema = new Schema();

$jobs = $schema->createTable("jobs");

$jobs->addColumn("id", "integer", array("unsigned" => true, "notnull" => true, "autoincrement" => true));
$jobs->addColumn("name", "string", array("length" => 128, "notnull" => true));
$jobs->addColumn("task", "string", array("length" => 128, "notnull" => true));
$jobs->addColumn("description", "text", array("default" => null));
$jobs->addColumn("min", "string", array("length" => 16, "notnull" => true));
$jobs->addColumn("hour", "string", array("length" => 16, "notnull" => true));
$jobs->addColumn("dayofmonth", "string", array("length" => 16, "notnull" => true));
$jobs->addColumn("month", "string", array("length" => 16, "notnull" => true));
$jobs->addColumn("dayofweek", "string", array("length" => 16, "notnull" => true));
$jobs->addColumn("year", "string", array("length" => 16, "notnull" => true));
$jobs->addColumn("parameters", "text", array("default" => null));
$jobs->addColumn("niceness", "integer", array("default" => 0));
$jobs->addColumn("maxtime", "integer", array("default" => null));
$jobs->addColumn("enabled", "boolean", array("default" => false));
$jobs->addColumn("firstrun", "integer", array("length" => 64, "notnull" => true));
$jobs->addColumn("lastrun", "integer", array("length" => 64, "default" => null));

$worklogs = $schema->createTable("worklogs");
$worklogs->addColumn("id", "integer", array("unsigned" => true, "notnull" => true, "autoincrement" => true));
$worklogs->addColumn("pid", "integer", array("unsigned" => true, "notnull" => true));
$worklogs->addColumn("jid", "integer", array("unsigned" => true, "notnull" => false, "default" => null));
$worklogs->addColumn("name", "string", array("length" => 128, "notnull" => true));
$worklogs->addColumn("task", "string", array("length" => 128, "notnull" => true));
$worklogs->addColumn("parameters", "text", array("default" => null, "notnull" => false));
$worklogs->addColumn("status", "string", array("length" => 12, "notnull" => true));
$worklogs->addColumn("success", "boolean", array("default" => false, "notnull" => false));
$worklogs->addColumn("result", "text", array("default" => null, "notnull" => false));
$worklogs->addColumn("start", "integer", array("length" => 64, "notnull" => true));
$worklogs->addColumn("end", "integer", array("length" => 64, "default" => null, "notnull" => false));
$worklogs->addForeignKeyConstraint($jobs, array("jid"), array("id"), array("onUpdate" => "NO ACTION", "onDelete" => "SET NULL"));

$queue = $schema->createTable("queue");
$queue->addColumn("id", "integer", array("unsigned" => true, "notnull" => true, "autoincrement" => true));
$queue->addColumn("name", "string", array("length" => 128, "notnull" => true));
$queue->addColumn("task", "string", array("length" => 128, "notnull" => true));
$queue->addColumn("parameters", "text", array("default" => null));

// populate tables
$queries = $schema->toSql($platform);

foreach ($queries as $query) {
    $conn->executeQuery($query);
}
*/
