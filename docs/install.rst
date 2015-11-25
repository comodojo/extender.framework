Installing extender
===================

.. highlight:: php

.. _extender.project: https://github.com/comodojo/extender.project
.. _composer: https://getcomposer.org/

Comodojo extender could be installed via `composer`_, directly using `extender.project`_ package or as a standalone library.

Requirements
************

To work properly, extender requires a cli-enabled PHP installation >=5.3.9 and a database; by default, extender auto-create a new SQLite3 database, but this behaviour can be changed easly.

Basic installation
******************

First install `composer`_, then:

- install extender from `extender.project`_ package (this will install a new instance of extender and required dependencies in "extender" folder):

.. code:: bash

    composer create-project comodojo/extender.project extender

- install extender as a standalone library:

.. code:: bash

    composer require comodojo/extender.framework

Database setup
**************

Extender relies on `comodojo/database` library for database handling. Before running extender for the first time, you need to create tables for jobs definition and worklog archive.

The `econtrol.php system install` command does all the job, but first you need to define database type in `extender-config.php` configuration file. At the time of writing, extender is reported to work with SQLite and MySQL, but may work with others databases supported by `comodojo/database` lib.

Out of the bundle, framework will create a new SQLite database and required tables; this is the default comfiguration::

	define("EXTENDER_DATABASE_MODEL", "SQLITE_PDO");

	define("EXTENDER_DATABASE_HOST", "localhost");

	define("EXTENDER_DATABASE_PORT",1); // SQLite does not require a port

	define("EXTENDER_DATABASE_NAME", EXTENDER_DATABASE_FOLDER."extender.sqlite"); // databasse name is also filename for SQLite

	define("EXTENDER_DATABASE_USER", "comodojo");

	define("EXTENDER_DATABASE_PASS", "");

	define("EXTENDER_DATABASE_PREFIX", "extender_");

	define("EXTENDER_DATABASE_TABLE_JOBS", "jobs");

	define("EXTENDER_DATABASE_TABLE_WORKLOGS", "worklogs");

Using MySQL is strongly suggested if you plan to run more than 100 tasks @ day (worklog table will contain more than 3000 rows after a month).

In any case, a cleanup task that delete old worklogs could be a good choise (see [example tasks](#example-tasks) at the end of this document for a working example).

Finalize installation
*********************

After composer finishes its work, econtrol should be invoked to to finalize installation::

    ./econtrol.php system install

This command will create database tables as in configuration file. It is also possible to check configuration/environment using::

    ./econtrol.php system check

Extender is now ready to operate::

    $ ./econtrol.php system check

    Extender checks:
    ----------------

    Extender minimum parameters configured: PASSED
    Multiprocess support available: YES
    Daemon support (signaling): YES
    Extender database available and configured: YES

    Extender parameters:
    --------------------

    Framework path: /var/extender/
    Multiprocess enabled: 1
    Idle time (daemon mode): 1
    Max result bytes per task: 2048
    Max childs: 0
    Max child runtime: 600
    Parent niceness: default
    Childs niceness: default
