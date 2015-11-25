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

It does not accept any argument in input, but has some options that modify script output:

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

Refer to Run as a `daemon`_ chapter for more information.

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
