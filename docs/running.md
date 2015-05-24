# Running extender

By default, framework (if installed via [comodojo/extender.project](https://github.com/comodojo/extender.project) package) comes with two ready-to-run scripts:

- `extender.php`: main framework worker
- `econtrol.php`: command line controller used to install and manage extender

## extender.php

It can be runned as a one-time executable or in daemon mode.

It does not accept any argument in input, but has some options that modify script output:

1. `-h` - print help and exit

2. `-v` - enable verbose mode (log to standard output), useful for debugging

3. `-s` - show a brief summary of executed jobs

4. `-d` - enable daemon mode

## econtrol.php

The econtrol script contains commands used to interact with the framework.

Each command is an independent, parametrizable script; commands can be packed in bundles and deployed/updated directly via composer.