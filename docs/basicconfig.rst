Basic configuration
===================

.. highlight:: php

.. _extender.project: https://github.com/comodojo/extender.project
.. _PHP Process Control extensions: http://php.net/manual/en/refs.fileprocess.process.php
.. _psr-3: http://www.php-fig.org/psr/psr-3/
.. _comodojo/database: https://github.com/comodojo/database

Extender can be customized defining some predefined constants.

The file `configs/extender-config.php` in `extender.project`_ package contains the default set of parameters.

General properties
******************

EXTENDER_TIMEZONE
"""""""""""""""""
(string> Local timezone, to not rely on the system's timezone settings (optional if correctly configured in php.ini).::

	define("EXTENDER_TIMEZONE", "Europe/Rome");

.. note:: It is STRONGLY reccomended to set this parameter properly.

EXTENDER_REAL_PATH
""""""""""""""""""

Extender real path.::

	define("EXTENDER_REAL_PATH", realpath(dirname(__FILE__))."/../");

EXTENDER_MULTITHREAD_ENABLED
""""""""""""""""""""""""""""
Enable/disable multithread mode; this feaure REQUIRE `PHP Process Control extensions`_.::

	define("EXTENDER_MULTITHREAD_ENABLED", true);

EXTENDER_IDLE_TIME
""""""""""""""""""
Idle time, in seconds; in other words, how long extender should be idle between each extend() cycle.::

	define("EXTENDER_IDLE_TIME", 1);

EXTENDER_MAX_RESULT_BYTES
"""""""""""""""""""""""""
Max bytes extender should read from completed child processes, if multithread is enabled.::

	define("EXTENDER_MAX_RESULT_BYTES", 2048);

EXTENDER_MAX_CHILDS
"""""""""""""""""""
Max child process to fork, 0 to no limit.::

	define("EXTENDER_MAX_CHILDS", 0);

EXTENDER_MAX_CHILDS_RUNTIME
"""""""""""""""""""""""""""
Child process max runtime, in seconds (default 10min).::

	define("EXTENDER_MAX_CHILDS_RUNTIME", 600);

EXTENDER_PARENT_NICENESS
""""""""""""""""""""""""
Parent process niceness (if in multithread mode).::

	define("EXTENDER_PARENT_NICENESS", 0);

.. note:: This parameter is not defined by default.

.. note:: Values < 0 may require a privileged user.

EXTENDER_CHILD_NICENESS
"""""""""""""""""""""""

Child processes niceness (if in multithread mode).::

	define("EXTENDER_CHILD_NICENESS", 0);

.. note:: This parameter is not defined by default.

.. note:: Values < 0 may require a privileged user.

Logging
*******

EXTENDER_LOG_ENABLED
""""""""""""""""""""
Enable/disable logger.::

	define("EXTENDER_LOG_ENABLED", false);

EXTENDER_LOG_NAME
"""""""""""""""""
Logger name.::

	define("EXTENDER_LOG_NAME", "extender");

EXTENDER_LOG_TARGET
"""""""""""""""""""
Log target. If null, logger will log to standard output (alternative to -v option); if string, it will be the filename to log to.::

	define("EXTENDER_LOG_TARGET", "extender.log");

.. note:: verify filesystem permissions on log folder BEFORE enabling file logging.

EXTENDER_LOG_LEVEL
""""""""""""""""""
Log level, as in `psr-3`_.::

	define("EXTENDER_LOG_LEVEL", "ERROR");
	
Folders
*******

EXTENDER_LOG_FOLDER
"""""""""""""""""""
Logs folder.::

	define("EXTENDER_LOG_FOLDER", EXTENDER_REAL_PATH."logs/");

EXTENDER_DATABASE_FOLDER
""""""""""""""""""""""""
Database folder (if sqlite3).::

	define("EXTENDER_DATABASE_FOLDER", EXTENDER_REAL_PATH."database/");

EXTENDER_TASK_FOLDER
""""""""""""""""""""
Tasks folder.::

	define("EXTENDER_TASK_FOLDER", EXTENDER_REAL_PATH."tasks/");

EXTENDER_PLUGIN_FOLDER
""""""""""""""""""""""
Plugins folder.::

	define("EXTENDER_PLUGIN_FOLDER", EXTENDER_REAL_PATH."plugins/");

EXTENDER_COMMAND_FOLDER
"""""""""""""""""""""""
Commands folder.::

	define("EXTENDER_COMMAND_FOLDER", EXTENDER_REAL_PATH."commands/");

EXTENDER_CACHE_FOLDER
"""""""""""""""""""""
Cache folder.::

	define("EXTENDER_CACHE_FOLDER", EXTENDER_REAL_PATH."cache/");

Database configuration
**********************

EXTENDER_DATABASE_MODEL
"""""""""""""""""""""""
Database model. Currently, extender is tested on MySQL and SQLite3 databases, but may work also with models supported by `comodojo/database`_ lib.::

	define("EXTENDER_DATABASE_MODEL", "SQLITE_PDO");

.. note:: safe choices are MYSQLI, MYSQL_PDO or SQLITE_PDO (default)

EXTENDER_DATABASE_HOST
""""""""""""""""""""""
Database host.::

	define("EXTENDER_DATABASE_HOST", "localhost");

EXTENDER_DATABASE_PORT
""""""""""""""""""""""
Database port.::

	define("EXTENDER_DATABASE_PORT",1);

EXTENDER_DATABASE_NAME
""""""""""""""""""""""
Database name.::

	define("EXTENDER_DATABASE_NAME", EXTENDER_DATABASE_FOLDER."extender.sqlite");

.. note:: in case of SQLITE_PDO database model, name SHOULD contain full path to db file.

EXTENDER_DATABASE_USER
""""""""""""""""""""""
Database user.::

	define("EXTENDER_DATABASE_USER", "comodojo");

EXTENDER_DATABASE_PASS
""""""""""""""""""""""
Database password.::

	define("EXTENDER_DATABASE_PASS", "");

EXTENDER_DATABASE_PREFIX
""""""""""""""""""""""""
Database tables' prefix.::

	define("EXTENDER_DATABASE_PREFIX", "extender\_");

EXTENDER_DATABASE_TABLE_JOBS
""""""""""""""""""""""""""""
Jobs table name.::

	define("EXTENDER_DATABASE_TABLE_JOBS", "jobs");

EXTENDER_DATABASE_TABLE_WORKLOGS
""""""""""""""""""""""""""""""""
Worklogs table name.::

	define("EXTENDER_DATABASE_TABLE_WORKLOGS", "worklogs");

Customizing framework
*********************

EXTENDER_CUSTOM_DESCRIPTION
"""""""""""""""""""""""""""
Custom description to show in command line.::
	
	define("EXTENDER_CUSTOM_DESCRIPTION", "My personalized version of extender");

.. note:: This parameter is not defined by default.

EXTENDER_CUSTOM_ASCII
"""""""""""""""""""""
Custom fancy logo to show in command line.::
	
	define("EXTENDER_CUSTOM_ASCII", "assets/logo.ascii");

.. note:: This parameter is not defined by default.

EXTENDER_CUSTOM_VERSION
"""""""""""""""""""""""
Custom version to show in command line.::

	define("EXTENDER_CUSTOM_VERSION", "1.2.3");

.. note:: This parameter is not defined by default.
