# mach3queue

A php queue system

## Installation

First, add the package in composer.
```json
{
  "require": {
    "mach3builders/mach3queue": "^1.0"
  },
}
```

```bash
composer install
```

Now run the following command to publish the configuration file.

```bash
./vendor/bin/queue publish
```

In the root of your project you will find a new file called `queue.php`.
In this file you can setup the supervisors and the workers for the queue.
The config file explains itself.
You have to at least change the bootstrap location to your own bootstrap file
so that your whole application is accessible from the queue, this has to be a absolute path.

```php
'bootstrap' => __DIR__.'/vendor/bootstrap.php',
```

Then in your own bootstrap you need to configure the queue so you can access it in your application.

```php
use Mach3queue\Queue\QueueManager as Queue;

$queue = new Queue;
$queue->setConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'database',
    'username' => 'username',
    'password' => 'password',
]);
```

Now because the queue has access to your database, run the following command to install the database tables.

```bash
./vendor/bin/queue install
```


## Usage
To add a new job to the queue you can use the `add` method. This will add the given queueable to the default queue.

```php
use Mach3queue\Queue\QueueManager as Queue;

Queue::addJob(new Queueable);
```

To add a job to a specific queue you can use the `on` method.

```php
use Mach3queue\Queue\QueueManager as Queue;

Queue::on('deploy')->addJob(new Queueable)
```

To create a new queueable your class needs to implement the `Mach3queue\Queue\Queueable`.
If you need to pass data to you `Queueable` you can do so through the `__construct` method.

```php
use Mach3queue\Queue\Queueable;

class FakeEmptyQueueable implements Queueable
{
    public int $id;
    
    public function __construct(int $id)
    {
        $this->id = $id;
    }
    
    public function handle(): void
    {
        // Do something with the id
    }
}
```

### After event

You can add a method to the job to be executed after the job has finished.
With this you can use your own logic after a job has either succeeded or failed.

```php
use Mach3queue\Job;
use Mach3queue\Queue\QueueManager as Queue;

Queue::addJob(new Queueable)
    ->after(function (Job $job) {
        // Do something
    });
```

The job has a method called status that will return an enum of the status of the job.

```php
use Mach3queue\Job;
use Mach3queue\Job\Status;

$job->status();

// Returns: Mach3queue\Job\Status
Status::COMPLETED;
Status::FAILED;

// these also exists, but will probably never be the case in the after call.
Status::PROCESSING;
Status::PENDING;
Status::UNKNOWN;
```

## Dashboard
The package comes with a dashboard to monitor the queue.
To view it you can get the html from it through the following code:

```php
use Mach3queue\Dashboard\Dashboard;

echo Dashboard::parse();
```

Make this accessible to view where you want within your own application.
If you want the jobs to show information about your `Queueable` you have to make the properties public.
All public properties will be shown as a label.


## Commands
These are the commands you can run in the terminal to manage the queue.

```bash
# To publish the configuration file

./vendor/bin/queue publish
```

```bash
# To prepare the database

./vendor/bin/queue install
```
```bash
# To start the queue

./vendor/bin/queue start
```
```bash
# To gracefully stop all the current queues and create 
# new workers with the new state of your application.

./vendor/bin/queue restart
```
```bash
# To gracefully stop the whole queue system.

./vendor/bin/queue terminate
```

---

## Deamon on a server

To run and monitor the queue on a server on a production environment we need to install and configure `supervisor`.
It is a process monitor for the Linux operating system, and will automatically restart the queue when it is stopped.

### installation
To install it on an Ubuntu server, you can use the following command.

```bash
sudo apt-get install supervisor
```

### Configuration
To configure the supervisor you need to create a new file in the `/etc/supervisor/conf.d` directory.
You can name the file whatever you want, but it needs to have the `.conf` extension.
This is a configuration file for the supervisor that you can use.

```bash
[program:queue]
process_name=%(program_name)s
command=php /home/websites/example.com/vendor/bin/queue start
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/home/websites/example.com/queue.log
stopwaitsecs=3600
```
Make sure the `stopwaitsecs` is set to a high number so that the queue has time to finish the jobs before it is stopped.
See http://supervisord.org/configuration.html for more information on the configuration.

### Starting the supervisor

After you have created the configuration file you can start the supervisor with the following commands.

```bash
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start queue
```

For full documentation on supervisor see http://supervisord.org/index.html