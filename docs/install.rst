Installing extender
===================

.. _extender project package: https://github.com/comodojo/extender
.. _composer: https://getcomposer.org/

The comodojo extender framework can be installed using `composer`_ as a product (using the dedicated `extender project package`_) or as a library.

To install it as a product:

.. code:: bash

    composer create-project comodojo/extender extender

Or, to intall it as a library in your own project:

.. code:: bash

    composer require comodojo/extender.framework

Requirements
------------

To work properly, comodojo/extender.framework requires PHP >=5.6.0 (cli enabled).

Following PHP extension are also required:

- ext-posix: PHP interface to \*nix Process Control Extensions
- ext-pcntl: process Control support in PHP
- ext-shmop: read, write, create and delete Unix shared memory segments
- ext-sockets: low-level interface to the socket communication functions

A database is not required but highly recommended; by default, extender creates a new SQLite3 database if no external database is specified.

Finalizing installation
-----------------------

TBW
