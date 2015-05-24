Comodojo extender is a database driven, multiprocess, (pseudo) cron tasks scheduler written in PHP.

It supports multiprocessing via [PHP Process Control extensions](http://php.net/manual/en/refs.fileprocess.process.php) and is designed to work with different databases.

## Requirements

To work properly, extender requires a cli-enabled PHP installation >=5.3.0 and a database; by default, extender auto-create a new SQLite3 database, but this behaviour can be changed easly.

## Basic concepts

Framework is structured on top of following main concepts:

* A task is a self-contained PHP script that does some work and returns a brief string as result. A task should extend the `Task` class in `\Comodojo\Extender\Task` namespace.

* A job is scheduled activity that refers to one task and may provide parameters to it. Jobs can be added or removed via [econtrol](#econtrol).

* A unit of work is an atomic task execution according to what declared in a job; each unit of work writes a row on worklog table independently.

* Tasks, commands (for [econtrol](#econtrol) command line interface) and plugins can be packed in packages and installed/updated/removed via composer.