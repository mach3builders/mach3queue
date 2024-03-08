# mach3queue

A php queue system

## Installation

First, add the package in composer.
Note the repositories section is required to pull the package from github because it is a private repository.

```json
{
  "require": {
    "mach3builders/mach3queue": "dev-main"
  },
  "repositories": [
    {
      "type": "vcs",
      "url":  "git@github.com:mach3builders/mach3queue.git"
    }
  ]
}
```

Then run the composer install or update command with the `-n` flag.

```bash
composer install -n
```

Now run the following command to publish the configuration file and prepare the database.

```bash
./vendor/bin/queue install
```

And to run the queue you can use the following command. But make sure you have configured the queue first.

```bash
./vendor/bin/queue
```

**Note:** If you want to install the package without the `-n` flag see the instructions at the bottom of the page.

## Configuration and setup
In the root of your project you will find a new file called `queue.php`.
In this file you can setup the supervisors and the workers for the queue.
The config file explains itself.
You have
to at least change the bootstrap location to your own bootstrap file
so that your whole application is accessible from the queue.

```php
'bootstrap' => './vendor/autoload.php'
```

Then in your bootstrap you need to configure the queue so you can access it in your application.

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

## Dashboard
The package comes with a dashboard to monitor the queue.
To view it you can get the html from it through the following code:

```php
use Mach3queue\Dashboard\Dashboard;

echo Dashboard::parse();
```

Make this accessible to view where you want within your own application.

## Commands
These are the commands you can run in the terminal to manage the queue.

```bash
# To start the queue

./vendor/bin/queue
```
```bash
# To publish the configuration file and prepare the database

./vendor/bin/queue install
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

---

## Installation without the `-n` flag
The `-n` flag is used to avoid the token request.
If you want to install it without the `-n` flag you have to set up a personal access token.
The way you set up a personal access token is by starting to running it without the `-n` flag. 

```bash
composer install
```
And after that you will be prompted with the message saying 

```bash
Could not fetch https://api.github.com/repos/mach3builders/mach3queue, please review your configured GitHub OAuth token or enter a new one to access private repos
When working with _public_ GitHub repositories only, head to "https://github.com/settings/tokens/new?scopes=&description=x" to retrieve a token.
This token will have read-only permission for public information only.
When you need to access _private_ GitHub repositories as well, go to "https://github.com/settings/tokens/new?scopes=repo&description=x"
Note that such tokens have broad read/write permissions on your behalf, even if not needed by Composer.
Tokens will be stored in plain text in "x/.composer/auth.json" for future use by Composer.
For additional information, check https://getcomposer.org/doc/articles/authentication-for-private-packages.md#github-oauth
Token (hidden):
```
Click the second link which will open a new tab in your browser with all the `repo` checkboxes checked.
If that is not the case, check all of them yourself. 
Then click the `Generate token` button.
Then copy the token and paste it in the terminal and press enter.
Now you are all set to use the package. 

