# Installing extender

Comodojo extender could be installed via composer, using dedicated [comodojo/extender.project](https://github.com/comodojo/extender.project) package.

## Requirements

To work properly, extender requires a cli-enabled PHP installation >=5.3.0 and a database; by default, extender auto-create a new SQLite3 database, but this behaviour can be changed easly.

## Basic installation

First [install composer](https://getcomposer.org/), then create a new extender/project using this command:

``php composer.phar create-project comodojo/extender.project extender``

This will install a new instance of extender and required dependencies in "extender" folder. 

## Database setup

Extender relies on `comodojo/database` library for database handling. Before running extender for the first time, you need to create tables for jobs definition and worklog archive.

The `econtrol.php install` command does all the job, but first you need to define database type in `extender-config.php` configuration file. At the time of writing, extender is reported to work with SQLite and MySQL, but may work with others databases supported by `comodojo/database` lib.

Out of the bundle, framework will create a new SQLite database and required tables; this is the default comfiguration:

```php

define("EXTENDER_DATABASE_MODEL", "SQLITE_PDO");

define("EXTENDER_DATABASE_HOST", "localhost");

define("EXTENDER_DATABASE_PORT",1); // SQLite does not require a port

define("EXTENDER_DATABASE_NAME", EXTENDER_DATABASE_FOLDER."extender.sqlite"); // databasse name is also filename for SQLite

define("EXTENDER_DATABASE_USER", "comodojo");

define("EXTENDER_DATABASE_PASS", "");

define("EXTENDER_DATABASE_PREFIX", "extender_");

define("EXTENDER_DATABASE_TABLE_JOBS", "jobs");

define("EXTENDER_DATABASE_TABLE_WORKLOGS", "worklogs");

```

Using MySQL is strongly suggested if you plan to run more than 100 tasks @ day (worklog table will contain more than 30000 rows after a month).

In any case, a cleanup task that delete old worklogs could be a good choise (see [example tasks](#example-tasks) at the end of this document for a working example).