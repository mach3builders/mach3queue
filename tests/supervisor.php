<?php

use Mach3queue\Action\PrepareTables;
use Mach3queue\Console\SupervisorCommand;
use Mach3queue\Queue\QueueManager as Queue;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/../vendor/autoload.php';

new SupervisorCommand();

$queue = new Queue;
$queue->setConnection([
    'driver' => 'sqlite',
    'host' => 'localhost',
    'database' => ':memory:',
    'username' => 'test',
    'password' => 'test',
]);
$queue->setAsGlobal();

$input = new ArgvInput($argv);
$output = new ConsoleOutput;

ray(getmypid());

(new PrepareTables)->execute();
(new SupervisorCommand())->run($input, $output);