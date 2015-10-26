Plugins
=======

In Extender, plugins represent a safe way to modify framework behaviour hooking functions to embedded events.

Let's take an example. Imagine to want extender to print current version each time it's invoked::

	<?php namespace My\Plugins;

	class TestPlugin {

		static public function OnExtenderReady(\Comodojo\Extender\Extender $extender) {

			echo "\n\nExtender is READY. Current version: ".$extender->getVersion()."\n\n";

		}

	}

This short piece of code:

- expects an Extender instance as parameter;
- print to screen a message containing current version.

To enable this plugin, just hook it at *extender* event (see next section for more information about events)::

	$extender->addHook("extender", "\My\Plugins\TestPlugin", "OnExtenderReady");

How extender emits events 
*************************

Extender emits events at each run cycle and/or a signal is received.

Run cycle related events are:

- *extender* - marks the frameworks has entered the running cycle and exposes the whole `\Comodojo\Extender\Extender` class without expecting any return value;
- *extender.tasks* - provides and expects a `\Comodojo\Extender\TasksTable` object;
- *extender.schedule* - provides and expects a `\Comodojo\Extender\Scheduler\Schedule` object;
- *extender.result* - marks the framework has ended the running cycle and exposes collected results;

Signal related events follow this schema:

	extender.signal.[*POSIX_SIGNAL*]

For example *extender.signal.SIGUSR1* is emitted when a SIGUSR1 is received.

All these events can be used in plugins to alter/customize the framework's behaviour.

.. note:: *SIGTERM*, *SIGINT*, *SIGTSTP* and *SIGCONT* events are used by the framework's embedded functions and **are not pluggable**, so **no event is emitted if one of these signals is received**.

Plugin bundles
**************

Plugins can be packed in bundles and installed using composer.

Creating a bundle is all about packaging plugins in the right way and defining a valid *composer.json* file. The `ExtenderInstallerActions.php` script will do all job of registering/updating/removing included plugins.

To achive this, installer expects:

- the type of package declared as *extender-plugins-bundle* or *comodojo-bundle*;
- plugin classes autoloaded by composer;
- **extra** field of *composer.json* populated with a *comodojo-plugins-load* or (preferably) a *extender-plugin-load* subfield, containing class, (eventually) method and event to hook plugin to.

So, for our *TestPlugin* the structure of package will be::

	mytasks/
		- src/
			- TestPlugin.php
		- composer.json

And the *composer.json*::

	{
	    "name": "my/plugins",
	    "description": "My first plugins' bundle",
	    "type": "extender-plugins-bundle",
	    "extra": {
	        "extender-plugin-load": [
	        	{
	        		"event": "extender",
	        		"class": "\\My\\Plugins\\TestPlugin",
	        		"method": "OnExtenderReady"
	        	}    
	        ]
	    },
	    "autoload": {
	        "psr-4": {
	             "My\\Plugins\\": "src"
	         }
	    }
	}

.. note:: Plugins are loaded and activated immediately; they cannot be paused or disabled, but only removed deleting composer dependency.