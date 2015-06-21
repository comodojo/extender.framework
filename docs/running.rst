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

2. `-v` - enable verbose mode (log to standard output), useful for debugging

3. `-s` - show a brief summary of executed jobs

4. `-d` - enable daemon mode

econtrol.php
************

The econtrol script contains commands used to interact with the framework.

Each command is an independent, parametrizable script; commands can be packed in bundles and deployed/updated directly via composer.

It has two default options:

1. `-h` - print help and exit

2. `-v` - show the program version and exit

The default set of commands (`_extender.commandsbundle.default`_) is installed by default. It contains following commands:

- *check*: Perform multiple checks on system configuration
- *install*: Perform first installation task (aliases: inst, in)
- *tasks*: List available tasks (aliases: task, tk)
- *execute*: Execute a tasks without invoking extender (aliases: exe, exec)
- *jobs*: List scheduled jobs (alias: job)
- *add*: Add a job to scheduler
- *del*: Delete a job
- *enable*: Enable a job (alias: ena)
- *disable*: Disable a job (alias: dis)
- *worklogs*: Display worklogs table (aliases: wrks, wkls)
- *worklog*: Display detailed information about a worklog (aliases: wrk, wkl)
- *status*: Get extender status (if in daemon mode) (alias: st)
- *pause*: Pause extender (if running and in daemon mode) (alias: pau)
- *resume*: Resume extender (if paused and in daemon mode) (alias: st)
- *export*: Export whole job list to file (alias: exp)
- *import*: Import job list from file (alias: imp)
