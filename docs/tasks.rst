Creating Tasks
==============

.. _extender.project: https://github.com/comodojo/extender.project

Tasks are the core components of extender framework.

A task is a self-contained PHP script that does some work and returns a brief string as result. It can be called from one (or more) jobs or directly from econtrol.

Anatomy of a task
*****************

A task should:

- extend the `\Comodojo\Extender\Task\Task` abstract class;
- implement the `run()` method;
- return a brief string as result (limited by `EXTENDER_MAX_RESULT_BYTES` constant).

A simple *HelloWorld* task could be defined as::

    <?php namespace My\Tasks;

    use \Comodojo\Extender\Task\Task;
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

    $extender->addTask([name], [class], [description])

Where:

- *name* is the name the task will have into the framework (a string of alphanumeric characters, no spaces, possibly in CamelCase);
- *class* is the full (namespaced) class of task;
- *description* is a brief description of what the task will do;

.. warning:: Extender assumes that task class is autoloaded by composer. Do not forget to registering your class into *composer.json* or task execution will generate error.

.. note:: Preconfigure autoloader sets the `\Comodojo\Extender\Task` namespace as directory `EXTENDER_TASK_FOLDER` defined into `extender.project`_. If defined in this directory (not-bundled tasks), there is nothing to declare to autoloade.

So, the syntax to call our `HelloWorldTask` will be something like::

    $extender->addTask("helloworld", "\\Comodojo\\Extender\\Task\\HelloWorldTask", "Greetings from extender");

.. note:: The `extender.project`_ has a specific configuration file to register tasks called *extender-tasks-config.php*.

Tasks bundles
*************

Tasks can be packed in bundles and installed using composer.

Creating a bundle is all about packaging tasks in the right way and defining a valid *composer.json* file. The `ExtenderInstallerActions.php` script will do all job of registering/updating/removing included tasks.

To achive this, installer expects:

- the type of package declared as *extender-tasks-bundle* or *comodojo-bundle*;
- task classes autoloaded by composer;
- **extra** field of *composer.json* populated with a *comodojo-tasks-register* or (preferably) a *extender-task-register* subfield, containing name, target, description and (eventually) class of single tasks.

So, for our *HelloWorldTask* the structure of package will be::

	mytasks/
		- tasks/
			- HelloWorldTask.php
		- composer.json

And the *composer.json*::

	{
	    "name": "my/tasks",
	    "description": "My first tasks' bundle",
	    "type": "extender-tasks-bundle",
	    "extra": {
	        "extender-task-register": [
	        	{
	        		"name": "HelloWorld",
	        		"class": "\\My\\Tasks\\HelloWorldTask",
	        		"description": "Greetings from extender"
	        	}    
	        ]
	    },
	    "autoload": {
	        "psr-4": {
	             "My\\Tasks\\": "tasks"
	         }
	    }
	}

That's all, our task is ready to be executed::

    $ ./econtrol.php tasks

    Available tasks:
    ---------------
    
    +-----------------------------+---------------------------------------------+
    | Name                        | Description                                 |
    +-----------------------------+---------------------------------------------+
    | HelloWord                   | Greetings from extender                     |
    +-----------------------------+---------------------------------------------+
    

