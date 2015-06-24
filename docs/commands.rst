Defining commands
=================

.. _extender.project: https://github.com/comodojo/extender.project
.. _extender.commandsbundle.default: https://github.com/comodojo/extender.commandsbundle.default
.. _api: https://api.comodojo.org/extender/
.. _pear/console_commandline: https://github.com/pear/Console_CommandLine
.. _package documentation: http://pear.php.net/package/Console_CommandLine/docs

Every command that econtrol provides is an independent, parametrizable PHP script. The `extender.commandsbundle.default`_ package contains basic commands used to interact with the framework.

Commands can be defined by user into the `EXTENDER_COMMAND_FOLDER` or packed in bundles and installed directly via composer.

Writing additional commands
***************************

A command is essentially a class that implements the `\Comodojo\Extender\CommandCommandInterface` and is defined in the same namespace. The `Comodojo\Extender\Command\StandardCommand` class can be useful to avoid common methods definition (is, essentially, a trait defined as a class for compatibility reasons).

.. note:: Take a look at the `api`_ to know all the method that your command should implement.

Supposing to extend the `Comodojo\Extender\Command\StandardCommand` class, a command should only implement the `execute()` method.

Let's take an "hello world" example.::

	<?php namespace Comodojo\Extender\Command;

	class helloworkcommand extends StandardCommand implements CommandInterface {

	    public function execute() {

	    	// the getOption() method can be used to retrieve options provided to the command
	        $test = $this->getOption("test");

	        // same for the arguments, with getArgument()
	        $to = $this->getArgument("to");

	        $to = is_null($to) ? "World" : $to;

	        // the color object can be used to add colors to command's output
	        return $this->color->convert("\n%gHello " . $to . "!%n");

	    }

	}

Once defined, a command should be registered into the framework. The *commands-config.php* file can be used for this purpose.

The format is the following::

	$extender->addCommand("helloworkcommand", array(
	    'description' => 'Greetings from comodojo extender',
	    'aliases'     => array('hw'),
	    'options'     => array(
	        'test'   =>  array(
	           'short_name'  => '-t',
	           'long_name'   => '--test',
	           'action'      => 'StoreTrue',
	           'description' => 'Void command option'
	        )
	    ),
	    'arguments'   => array(
	        'to'   =>  array(
	           'choices'     => array(),
	           'multiple'    => false,
	           'optional'    => true,
	           'description' => 'hello to...'
	        )
	    )
	));

.. note:: Extender relies from `pear/console_commandline`_ package to handle command line operations. Take a look at package` documentation`_ to know more.

Command Bundles
***************

Creating a bundle of commands is quite easy.

First, let's take a look at the (proposed) directory structure of a package::

	mybundle/
		- commands/
			- helloworkcommand.php
			- customcommand.php
		- composer.json

Commands' classes should be autoloaded (using composer); in addition, something should be written in *commands-config.php* file. The project package does all the job automatically using **extra** field of *composer.json*.

To enable this feature, the package's type **should** be declared as *extender-commands-bundle* and the *extra* field should contain a *comodojo-commands-register* subfield.

So, the composer.json of *mybundle* package will be something like::

	{
	    "name": "my/mybundle",
	    "description": "My first commands bundle",
	    "type": "extender-commands-bundle",
	    "extra": {
	        "comodojo-commands-register": {
	            "customcommand": {
	                "description": "A custom command",
	                "aliases": [],
	                "options": {},
	                "arguments": {}
	            },
	            "helloworkcommand": {
	                "description": "Greetings from comodojo extender",
	                "aliases": ["hw"],
	                "options": {
	                    "force": {
	                        "short_name": "-t",
	                        "long_name": "--test",
	                        "action": "StoreTrue",
	                        "description": "Void command option"
	                    }
	                },
	                "arguments": {
	                    "to": {
	                        "choices": {},
	                        "multiple": false,
	                        "optional": true,
	                        "description": "hello to..."
	                    }
	                }
	            }
	        }
	    },
	    "autoload": {
	        "psr-4": {
	             "Comodojo\\Extender\\Command\\": "commands"
	         }
	    }
	}

Once installed, every should be in place to exec those commands using::

	./econtrol.php exec helloworkcommand Marvin

and view something like::

	(missing block)