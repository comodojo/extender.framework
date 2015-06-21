Comodojo extender docs
======================

.. image:: assets/extender-logo-250.png

.. _PHP Process Control extensions: http://php.net/manual/en/refs.fileprocess.process.php

Comodojo extender is a database driven, multiprocess, (pseudo) cron tasks scheduler written in PHP.

It supports multiprocessing via `PHP Process Control extensions`_ and is designed to work with different databases.

Extender is structured on top of following main concepts:

- A task is a self-contained PHP script that does some work and returns a brief string as result. A task should extend the `Task` class in `\Comodojo\Extender\Task` namespace.

- A job is a scheduled activity that refers to one task and may provide parameters to it.

- A unit of work is an atomic task execution according to what declared in a job; each unit of work writes a row on worklog table independently.

- Task and jobs can be configured and managed via command line (econtrol.php command line interface).

- Tasks, commands and plugins can be packed in packages and installed/updated/removed via composer.

.. toctree::
   :maxdepth: 2

   install
   basicconfig
   running
   daemonizing
   tasks
   jobs
   commands
   plugins


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

