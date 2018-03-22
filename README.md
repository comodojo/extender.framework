Comodojo extender
=================

[![Build Status](https://api.travis-ci.org/comodojo/extender.framework.png)](http://travis-ci.org/comodojo/extender.framework) [![Latest Stable Version](https://poser.pugx.org/comodojo/extender.framework/v/stable)](https://packagist.org/packages/comodojo/extender.framework) [![Total Downloads](https://poser.pugx.org/comodojo/extender.framework/downloads)](https://packagist.org/packages/comodojo/extender.framework) [![Latest Unstable Version](https://poser.pugx.org/comodojo/extender.framework/v/unstable)](https://packagist.org/packages/comodojo/extender.framework) [![License](https://poser.pugx.org/comodojo/extender.framework/license)](https://packagist.org/packages/comodojo/extender.framework) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/comodojo/extender.framework/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/comodojo/extender.framework/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/comodojo/extender.framework/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/comodojo/extender.framework/?branch=master)

Daemonizable, database driven, multiprocess, (pseudo) cron task scheduler in PHP.

** This is the development branch, please do not use it in production**

It supports multiprocessing via [PHP Process Control extensions](http://php.net/manual/en/refs.fileprocess.process.php) and is designed to work with different databases.

For more information, visit [extender.comodojo.org](https://extender.comodojo.org).

## Installation

First, install [composer](https://getcomposer.org/). Then:

- install extender from [project package](https://github.com/comodojo/extender.project):

        composer create-project comodojo/extender

- install extender as a standalone library:

        composer require comodojo/extender.framework

## Documentation

- [Docs](https://docs.comodojo.org/projects/extenderframework/)
- [API](https://api.comodojo.org/extender/)

## Contributing

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
