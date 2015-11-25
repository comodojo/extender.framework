Running extender
================

.. highlight:: php

.. _extender.project: https://github.com/comodojo/extender.project
.. _extender.commandsbundle.default: https://github.com/comodojo/extender.commandsbundle.default

When created, a new instance of `extender.project`_ includes two ready-to-run scripts:

- `extender.php`: main framework worker
- `econtrol.php`: command line controller used to install and manage extender

extender.php
------------

It can be runned as a one-time executable or in daemon mode.

It does not accept any argument in input, but has some options that modify script output::

    $ ./extender.php -h
       ______                                __            __
      / ____/ ____    ____ ___   ____   ____/ / ____      / /  ____
     / /     / __ \  / __ `__ \ / __ \ / __  / / __ \    / /  / __ \
    / /___  / /_/ / / / / / / // /_/ // /_/ / / /_/ /   / /  / /_/ /
    \____/  \____/ /_/ /_/ /_/ \____/ \__,_/  \____/  _/ /   \____/
    ----------------------------------------------  /___/  ---------
                     __                      __
      ___    _  __  / /_  ___    ____   ____/ / ___    _____
     / _ \  | |/_/ / __/ / _ \  / __ \ / __  / / _ \  / ___/
    /  __/ _>  <  / /_  /  __/ / / / // /_/ / /  __/ / /
    \___/ /_/|_|  \__/  \___/ /_/ /_/ \__,_/  \___/ /_/
    --------------------------------------------------------

    Daemonizable, database driven, multiprocess, (pseudo) cron task scheduler

    Version: 1.0.0

    Available options:
    ------------------
     -v : verbose mode
     -V : debug mode
     -s : show summary of executed jobs (if any)
     -d : run extender in daemon mode
     -h : show this help

Running as a daemon
"""""""""""""""""""

`-v` enable the daemon mode, in witch extender will run in a infinite loop. Idle time (default 1 sec) can be modified `EXTENDER_IDLE_TIME` configuration constant.

Refer to :doc:`/daemonizing` chapter for more information.

Verbose and debug mode
""""""""""""""""""""""

`-v` and `-V` options may be used show logs directly from commandline. It is not reccomended to enable one of these options in daemon mode.

Instead, the `EXTENDER_LOG_ENABLED` and `EXTENDER_LOG_TARGET` configuration options may collect same information directly into a resource.

econtrol.php
------------

The econtrol script contains commands used to interact with the framework.

Each command is an independent, parametrizable script; commands can be packed in bundles and deployed/updated directly via composer.

When launched, econtrol shows something like::

       ______                                __            __
      / ____/ ____    ____ ___   ____   ____/ / ____      / /  ____
     / /     / __ \  / __ `__ \ / __ \ / __  / / __ \    / /  / __ \
    / /___  / /_/ / / / / / / // /_/ // /_/ / / /_/ /   / /  / /_/ /
    \____/  \____/ /_/ /_/ /_/ \____/ \__,_/  \____/  _/ /   \____/
    ----------------------------------------------  /___/  ---------
                     __                      __
      ___    _  __  / /_  ___    ____   ____/ / ___    _____
     / _ \  | |/_/ / __/ / _ \  / __ \ / __  / / _ \  / ___/
    /  __/ _>  <  / /_  /  __/ / / / // /_/ / /  __/ / /
    \___/ /_/|_|  \__/  \___/ /_/ /_/ \__,_/  \___/ /_/
    --------------------------------------------------------

    Daemonizable, database driven, multiprocess, (pseudo) cron task scheduler


    Usage:
      ./econtrol.php [options]
      ./econtrol.php [options] <command> [options] [args]

    Options:
      -v, --verbose  turn on verbose output
      -V, --debug    turn on debug output
      -h, --help     show this help message and exit
      --version      show the program version and exit

    Commands:
      configuration  Backup and restore configuration (aliases: conf, config)
      execute        Execute a tasks (aliases: exe, exec)
      jobs           Manage jobs (alias: job)
      logs           Show logs (alias: log)
      system         System actions (alias: sys)
      tasks          Show tasks (alias: task)

Each command supports the `-v, --help` options to show a brief description about its usage.

configuration
"""""""""""""

This command can be used to backup or restore the current jobs' configuration in json format.

Usage:

.. code:: bash

    ./econtrol.php [options] configuration [options] <backup|restore> file

Examples:

.. code:: bash

    ./econtrol.php configuration backup /custom/path/backup.json

.. code:: bash

    ./econtrol.php configuration restore /custom/path/backup.json

execute
"""""""

Execute a task directly from econtrol, without specifying a job.

Parameters can be provided as a comma separated, not spaced, [option]=[value] string

Usage:

.. code:: bash

    ./econtrol.php [options] execute [options] <task> [parameters]

Examples:

.. code:: bash

    ./econtrol.php execute MyTask test=true,user=slartibartfast

jobs
""""

Manage jobs, including enable/disable actions.

Jobs are referenced using their names (unique key).

Usage:

.. code:: bash

    ./econtrol.php [options] jobs [options] [enable|disable|add|remove|show*] [name] [expression] [task] [description] [parameters]

Examples:

Print the current jobs' table:

.. code:: bash

    ./econtrol.php jobs

.. code:: bash

    ./econtrol.php jobs show

Extensive version:

.. code:: bash

    ./econtrol.php jobs -x

Add a new job:

.. code:: bash

    ./econtrol.php jobs add JobName "30 3 * * *" MyTask "An example job" test=false,user=slartibartfast

Delete a job:

.. code:: bash

    ./econtrol.php jobs remove JobName

Enable a job:

.. code:: bash

    ./econtrol.php jobs enable JobName

Logs
""""

Get/search logs, optionally filtered by:

- log id (wid)
- job id (jid)
- date/time/timerange (time)
- limit/offset (limit)

Usage:

.. code:: bash

    ./econtrol.php [options] logs [options] [wid, jid, time, limit, show*] [filter] [extra]

Examples:

Get last 10 logs:

.. code:: bash

    ./econtrol.php logs

Get the log num. 35:

.. code:: bash

    ./econtrol.php logs wid 35

Get last 30 logs started by job 5:

.. code:: bash

    ./econtrol.php logs jid 5 30

Get last 100 logs:

.. code:: bash

    ./econtrol.php logs limit 100

Get last 100 with an offset of 30:

.. code:: bash

    ./econtrol.php logs limit 100 30

Get logs for a specific date:

.. code:: bash

    ./econtrol.php logs time "10-10-2015"

Get logs for a datetime range:

.. code:: bash

    ./econtrol.php logs time "10-10-2015T11:00" "11-10-2015T20:00"

system
""""""

Perform system actions like install, reinstall (factory reset), check, get status, pause/restore daemon.

Usage:

./econtrol.php [options] system [options] <status|check|install|pause|resume>

Examples:

To install extender:

.. code:: bash

    ./econtrol.php system install

Factory reset:

.. code:: bash

    ./econtrol.php system install --force

Check daemon status

.. code:: bash

    ./econtrol.php system status

tasks
"""""

Show registered tasks.

Usage:

.. code:: bash

    ./econtrol.php [options] tasks [options]

Examples:

Show tasks:

.. code:: bash

    ./econtrol.php tasks

Extensive version:

.. code:: bash

    ./econtrol.php tasks -x
