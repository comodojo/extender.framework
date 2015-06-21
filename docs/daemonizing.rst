Run as a daemon
===============

.. highlight:: php

.. _PHP Process Control extensions: http://php.net/manual/en/refs.fileprocess.process.php
.. _pcntl: http://php.net/manual/en/refs.fileprocess.process.php
.. _POSIX signals: https://en.wikipedia.org/wiki/Unix_signal
.. _extender.project: https://github.com/comodojo/extender.project
.. _internal event subsystem: 

The `extender.php` worker can be runned as a daemon using the `-d` option from commandline.

In this case, it is important to remember that extender stores its own PID file automatically in `cache` folder, so init script should not reference on another file or overwrite the default one.

Another thing to consider is that `PHP Process Control extensions`_ are required to run extender as a daemon; if ext is not available, extender will return an error and exit with 1.

Init scripts
************

The `extender.project`_ package contains two examples of init.d scripts:

- *extender.init.deb.example* uses the debian/ubuntu `start-stop-daemon`, so it should be used in debian based OS;
- *extender.init.rhel.example* uses the `daemon` func, so it should be used in rhel/centos based OS or in any other OS that acts the same.

Daemon lifecycle
****************

If started with `-d` option, extender will, first:

- check for configuration/extensions requirements (such as `pcntl`_);
- load runtime informations (tasks, plugins, ...) via launcher (if any);
- wait for the launcher script to call `extend()` method.

.. note:: Framework itself does not inlcude a loop cycle; this is something the launcher script is in charge of. The constant `EXTENDER_IDLE_TIME` should be used to set the sleep time of daemon.

Once started successfully, the parent process runs in a loop in witch it checks if plan file exists and contains a valid future timestamp. If not, the task table is queried to know if there are planned job in queue.

If there is something to do, daemon will start executing jobs. In case of multithread enabled, the `EXTENDER_MAX_CHILDS` constant determine the number of max childs that will be forked.

.. note:: Extender is not a pure queue manager: childs are forked in group (in case of `EXTENDER_MAX_CHILDS` != 0) and no other thread will be raised until last group member has finished.

Interacting with daemon
***********************

In daemon mode, extender will act as a regular unix process, except for the fact that it will never exit (so it should be called with a final `&` from init script).

As a unix process, it will handle standard `POSIX signals`_.

Stopping and pausing
""""""""""""""""""""

Signals that will cause the daemon to stop:

- *SIGKILL* will close it immediately;
- *SIGTERM* and *SIGINT* will stop the daemon gracefully.

In last case, each running child will be forced to exit using a TERM signal. After few seconds (a lagger timeout of 5 seconds is defined `Runner\JobRunner.php`) the child will be killed using SIGKILL. 

To pause and resume the demon, *SIGTSTP* and *SIGCONT* can be used respectively.

Handling custom signals
"""""""""""""""""""""""

Custom signals can be catched and handled using the internal event subsystem.

In few words, to define a custom signal handler it is necessary to define a plugin that catch the respective event.

A brief example using lamba function:

    $extender->addHook("extender.signal.SIGUSR2", function($instance) {
        echo "Extender completed ".$instance->getCompletedProcesses." processes."
    });
    
Here's a list of currently pluggable events:

- SIGHUP,
- IGCHLD
- SIGUSR2
- SIGILL
- SIGTRAP
- SIGABRT
- SIGIOT
- SIGBUS
- SIGFPE
- SIGSEGV
- SIGPIPE
- SIGALRM
- SIGTTIN
- SIGTTOU
- SIGURG
- SIGXCPU
- SIGXFSZ
- SIGVTALRM
- SIGPROF
- SIGWINCH
- SIGIO
- SIGSYS
- SIGBABY
- SIGPOLL (if supported)
- SIGPWR (if supported)
- SIGSTKFLT (if supported)