Plugins
=======

In Extender, plugins represent a safe way to modify framework behaviour hooking functions to embedded events.

Let's take an example. Immagine to want extender to print current version each time it's invoked::

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

(missing content)

Plugin bundles
**************

Plugins can be packed in bundles and installed using composer.

Creating a bundle is all about packaging plugins in the right way and defining a valid *composer.json* file. The `ExtenderInstallerActions.php` script will do all job of registering/updating/removing included plugins.

To achive this, installer expects:

- the type of package declared as *extender-plugins-bundle*;
- plugin classes autoloaded by composer;
- **extra** field of *composer.json* populated with a *comodojo-plugins-load* object, containing class, (eventually) method and event to hook plugin to.

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
	        "comodojo-plugins-load": [
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