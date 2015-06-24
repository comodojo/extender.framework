Creating Tasks
==============

.. highlight:: php

.. _extender.project: https://github.com/comodojo/extender.project

Tasks are the core components of extender framework.

A task is a self-contained PHP script that does some work and returns a brief string as result. It can be called from one (or more) jobs or directly from econtrol.

Anatomy of a task
*****************

A task should:

- extend the `Task` class in `\Comodojo\Extender\Task` namespace;
- implement the `run()` method;
- return a brief string as result (limited bye `EXTENDER_MAX_RESULT_BYTES` constant).

A simple *HelloWorld* task could be defined as::

    <?php namespace Comodojo\Extender\Task;

    use \Comodojo\Exception\TaskException;
    
    class HelloWorldTask extends Task {

        public function run() {

            return "Hello world!";

        }

    }

.. note:: Throwing a `\Comodojo\Exception\TaskException` in case of error is not a constraint but is highly recommended.

Registering a task
******************

Once added, a task should be registered into extender using the `$extender->addTask()` method **before** the `$extender->extend()` or `$extender->process()`.

The syntax of this method is::

    $extender->addTask([name], [target], [description], [:class], [:relative])

Where:

- *name* is the name the task will have into the framework (a string of alphanumeric characters, no spaces, possibly in CamelCase);
- *target* is the filename of task;
- *description* is a brief description of what the task will do;
- *class* is the class declared into task file;
- *relative* a bool that determine if *target* is a filename or a full path.

Last two parameters are optional.

The first (*class*) can be used to specify the class declared into task file **only** if it differs from the file name (although namespace should the same).

The second (*relative*) can be used if the task file is in a different path from the default one AND is not declared into autoloader. Extender, in fact, will verify first that the class exists and, only if not available, will try to include it as a standalone script.

The syntax to call our `HelloWorldTask` will be something like::

    $extender->addTask("helloworld", "HelloWorldTask.php", "Greetings from extender", null, true);

.. note:: The `extender.project`_ has a specific configuration file to register tasks called *tasks-config.php*.

Tasks bundles
*************

Tasks can be packed in bundles and installed using composer.

Creating a bundle is all about packaging tasks in the right way and defining a valid *composer.json* file. The `ExtenderInstallerActions.php` script will do all job of registering/updating/removing included tasks.

To achive this, installer expects:

- the type of package declared as *extender-tasks-bundle*;
- tasks placed in a *tasks* directory;
- **extra** field of *composer.json* populated with a *comodojo-tasks-register* object, containing name, target, description and (eventually) class of single tasks.

So, for our *HelloWorldTask* the structure of package will be::

	mytasks/
		- tasks/
			- HelloWorldTask.php
		- composer.json

And the *composer.json*::

	{
	    "name": "my/mytasks",
	    "description": "My first tasks' bundle",
	    "type": "extender-tasks-bundle",
	    "extra": {
	        "comodojo-tasks-register": [
	        	{
	        		"name": "HelloWord",
	        		"target": "HelloWorldTask.php",
	        		"description": "Greetings from extender"
	        	}    
	        ]
	    },
	    "autoload": {
	        "psr-4": {
	             "Comodojo\\Extender\\Task\\": "tasks"
	         }
	    }
	}

That's all, our task is ready to be executed::

	(missing block)