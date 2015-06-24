Scheduling jobs
===============

.. _unix cron: https://en.wikipedia.org/wiki/Cron
.. _extender.project: https://github.com/comodojo/extender.project
.. _extender.commandsbundle.default: https://github.com/comodojo/extender.commandsbundle.default

A job is a scheduled activity that refers to one task and may provide parameters to it. Generally speaking, a job is a planned instance of a task in a defined moment.

Jobs are declared using econtrol and are stored into extender's database.

.. note:: If you're familiar with `unix cron`_, you will find few similarities; in a certain way, this definition of job is derived from cron.

Job's format
************

A job is technically composed by following values:

    [expression] [name] [task] [description] [:parameters]
    
Where:

- *expression* is a cron-compatible expression composed by:

    *    *    *    *    *    *
    -    -    -    -    -    -
    |    |    |    |    |    |
    |    |    |    |    |    + year [optional]
    |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
    |    |    |    +---------- month (1 - 12)
    |    |    +--------------- day of month (1 - 31)
    |    +-------------------- hour (0 - 23)
    +------------------------- min (0 - 59)

- *name* is the job name
- *descritpion* is a brief description of the job
- *parameters* are params provided to related task to customize its behaviour

Managing jobs
*************

The `extender.commandsbundle.default`_ package (installed automatically by `extender.project`_) contains few commands that can be used to manage jobs.

Add and remove a job
""""""""""""""""""""

To add a job:

    ./econtrol.php add "10 * * * * *" HelloWorldJob HelloWorld "Hourly greetings" -e
    
This will add a hourly (h:10) job and will enable it (-e option).

Optional parameters can be addedd (if job know how to handle it) using `key=value` format (comma separated):

    ./econtrol.php add "15 * * * * *" HelloWorldJob2 HelloWorld "Hourly greetings (w params)" "to=Slartibartfast,where=Magrathea"
    
To delete a job:

    ./econtrol.php del HelloWorld
    
List jobs
"""""""""

**Brief** format:

    ./econtrol.php jobs
    
This will produce something like:

    Available jobs:
    ---------------
    
    +----------------+-------------------------+-------------------+-----------------------------------------+---------+
    | Expression     | Name                    | Task              | Description                             | Enabled |
    +----------------+-------------------------+-------------------+-----------------------------------------+---------+
    | 10 * * * * *   | HelloWorldJob           | HelloWorld        | Hourly greetings                        | YES     |
    | 15 * * * * *   | HelloWorldJob2          | HelloWorld        | Hourly greetings (w params)             | NO      |
    +----------------+-------------------------+-------------------+-----------------------------------------+---------+

**Extensive** format:

    ./econtrol.php jobs -e
    
This will produce something like:

    Available jobs:
    ---------------
    
    +-------------+---------------------------------+
    | Name        | HelloWorldJob                   |
    +-------------+---------------------------------+
    | Expression  | 10 * * * * *                    |
    | Task        | HelloWorld                      |
    | Description | Hourly greetings                |
    | Enabled     | NO                              |
    | Lastrun     | Fri, 05 Dec 2014 03:10:01 +0100 |
    | Firstrun    | Tue, 30 Sep 2014 03:10:00 +0200 |
    +-------------+---------------------------------+
    | Parameters  | array (                         |
    |             | )                               |
    +-------------+---------------------------------+
    
    
    +-------------+---------------------------------+
    | Name        | HelloWorldJob2                  |
    +-------------+---------------------------------+
    | Expression  | 10 * * * * *                    |
    | Task        | HelloWorld                      |
    | Description | Hourly greetings (w params)     |
    | Enabled     | NO                              |
    | Lastrun     | Fri, 05 Dec 2014 03:10:01 +0100 |
    | Firstrun    | Tue, 30 Sep 2014 03:10:00 +0200 |
    +-------------+---------------------------------+
    | Parameters  | array (                         |
    |             |     "to" => "Slartibartfast",   |
    |             |     "where" => "Magrathea",     |
    |             | )                               |
    +-------------+---------------------------------+

Enable or disable a job
"""""""""""""""""""""""

    ./econtrol.php enable HelloWorldJob
    
    ./econtrol.php disable HelloWorldJob
    
Import and export jobs
""""""""""""""""""""""

Jobs can be exported and imported in a convenient json format with:

    ./econtrol.php export my-job-list.json

    ./econtrol.php import my-job-list.json
    
The `import` command has a [-c] option to clean jobs table before importing from file.

