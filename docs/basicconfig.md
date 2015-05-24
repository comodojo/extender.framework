# Basic configuration

Extender can be customized defining some predefined constants.

The file `configs/extender-config.php` in [comodojo/extender.project](https://github.com/comodojo/extender.project) package contains the default set of parameters.

## EXTENDER_TIMEZONE

Local timezone, to not rely on the system's timezone settings (optional if correctly configured in php.ini).

It is STRONGLY reccomended to set this parameter properly.

```php
define("EXTENDER_TIMEZONE", "Europe/Rome");
```

## EXTENDER_REAL_PATH

Extender real path.

```php
define("EXTENDER_REAL_PATH", realpath(dirname(__FILE__))."/../");
```

## EXTENDER_MULTITHREAD_ENABLED

Enable/disable multithread mode; this feaure REQUIRE PHP Process Control extension (PCNTL).

```php
define("EXTENDER_MULTITHREAD_ENABLED", true);
```

## EXTENDER_IDLE_TIME

Idle time, in seconds.

This constant determine how long extender should be idle between each extend() cycle.

```php
define("EXTENDER_IDLE_TIME", 1);
```

## EXTENDER_MAX_RESULT_BYTES

Max bytes extender should read from completed child processes, if multithread is enabled.

```php
define("EXTENDER_MAX_RESULT_BYTES", 2048);
```

## EXTENDER_MAX_CHILDS

Max child process to fork, 0 to no limit.

```php
define("EXTENDER_MAX_CHILDS", 0);
```

## EXTENDER_MAX_CHILDS_RUNTIME

Child process max runtime, in seconds (default 10min).

```php
define("EXTENDER_MAX_CHILDS_RUNTIME", 600);
```

## EXTENDER_PARENT_NICENESS

Parent process niceness (if in multithread mode).

Values < 0 may require a privileged user!

* PLEASE NOTE: * this parameter is not defined by default.

```php
define("EXTENDER_PARENT_NICENESS", 0);
```

## EXTENDER_CHILD_NICENESS

Child processes niceness (if in multithread mode).

Values < 0 may require a privileged user!

* PLEASE NOTE: * this parameter is not defined by default.

```php
define("EXTENDER_CHILD_NICENESS", 0);
```

## EXTENDER_LOG_ENABLED

Enable/disable logger.

```php
define("EXTENDER_LOG_ENABLED", false);
```

## EXTENDER_LOG_NAME

Logger name.

```php
define("EXTENDER_LOG_NAME", "extender");
```

## EXTENDER_LOG_TARGET

Log target:

- if NULL, logger will log to standard output (alternative to -v option)
- if string, it will be the filename to log to

* PLEASE NOTE: * verify filesystem permissions on log folder BEFORE enabling file logging

```php
define("EXTENDER_LOG_TARGET", "extender.log");
```

## EXTENDER_LOG_LEVEL

Log level, as in [http://www.php-fig.org/psr/psr-3/](http://www.php-fig.org/psr/psr-3/)

```php
define("EXTENDER_LOG_LEVEL", "ERROR");
```

## EXTENDER_LOG_FOLDER

Logs folder.

```php
define("EXTENDER_LOG_FOLDER", EXTENDER_REAL_PATH."logs/");
```

## EXTENDER_DATABASE_FOLDER

Database folder (if sqlite3).

```php
define("EXTENDER_DATABASE_FOLDER", EXTENDER_REAL_PATH."database/");
```

## EXTENDER_TASK_FOLDER

Tasks folder.

```php
define("EXTENDER_TASK_FOLDER", EXTENDER_REAL_PATH."tasks/");
```

## EXTENDER_PLUGIN_FOLDER

Plugins folder.

```php
define("EXTENDER_PLUGIN_FOLDER", EXTENDER_REAL_PATH."plugins/");
```

## EXTENDER_COMMAND_FOLDER

Commands folder.

```php
define("EXTENDER_COMMAND_FOLDER", EXTENDER_REAL_PATH."commands/");
```

## EXTENDER_CACHE_FOLDER

Cache folder.

```php
define("EXTENDER_CACHE_FOLDER", EXTENDER_REAL_PATH."cache/");
```

## EXTENDER_DATABASE_MODEL

Database model.

Currently, extender is tested on MySQL and SQLite3 databases, but may work also with models supported by comodojo/database lib.

* PLEASE NOTE: * safe choices are MYSQLI, MYSQL_PDO or SQLITE_PDO (default)

```php
define("EXTENDER_DATABASE_MODEL", "SQLITE_PDO");
```

## EXTENDER_DATABASE_HOST

Database host.

```php
define("EXTENDER_DATABASE_HOST", "localhost");
```

## EXTENDER_DATABASE_PORT

Database port.

```php
define("EXTENDER_DATABASE_PORT",1);
```

## EXTENDER_DATABASE_NAME

Database name.

* PLEASE NOTE: * in case of SQLITE_PDO database model, name SHOULD contain full path to db file.

```php
define("EXTENDER_DATABASE_NAME", EXTENDER_DATABASE_FOLDER."extender.sqlite");
```

## EXTENDER_DATABASE_USER

Database user.

```php
define("EXTENDER_DATABASE_USER", "comodojo");
```

## EXTENDER_DATABASE_PASS

Database password.

```php
define("EXTENDER_DATABASE_PASS", "");
```

## EXTENDER_DATABASE_PREFIX

Database tables' prefix.

```php
define("EXTENDER_DATABASE_PREFIX", "extender_");
```

## EXTENDER_DATABASE_TABLE_JOBS

Jobs table name.

```php
define("EXTENDER_DATABASE_TABLE_JOBS", "jobs");
```

## EXTENDER_DATABASE_TABLE_WORKLOGS

Worklogs table name.

```php
define("EXTENDER_DATABASE_TABLE_WORKLOGS", "worklogs");
```

## EXTENDER_CUSTOM_DESCRIPTION

Custom description to show in command line.

* PLEASE NOTE: * this parameter is not defined by default.

```php
define("EXTENDER_CUSTOM_DESCRIPTION", "My personalized version of extender");
```

## EXTENDER_CUSTOM_ASCII

Custom fancy logo to show in command line.

* PLEASE NOTE: * this parameter is not defined by default.

```php
define("EXTENDER_CUSTOM_ASCII", "assets/logo.ascii");
```

## EXTENDER_CUSTOM_VERSION

Custom version to show in command line.

* PLEASE NOTE: * this parameter is not defined by default.

```php
define("EXTENDER_CUSTOM_VERSION", "1.2.3");
```
