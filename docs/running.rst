Running extender
================

.. highlight:: php

.. _extender.project: https://github.com/comodojo/extender.project
.. _extender.commandsbundle.default: https://github.com/comodojo/extender.commandsbundle.default

When created, a new instance of `extender.project`_ includes two ready-to-run scripts:

- `extender.php`: main framework worker
- `econtrol.php`: command line controller used to install and manage extender

extender.php
************

It can be runned as a one-time executable or in daemon mode.

It does not accept any argument in input, but has some options that modify script output:

1. `-h` - print help and exit

2. `-v` - enable verbose mode

3. `-V` - enable debug mode

3. `-s` - show a brief summary of executed jobs

4. `-d` - enable daemon mode

econtrol.php
************

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
