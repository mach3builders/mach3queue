<?php

use Mach3queue\Action\PrepareTables;
use Mach3queue\Console\SupervisorCommand;
use Mach3queue\Queue\QueueManager as Queue;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/../vendor/autoload.php';

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

(new PrepareTables)();
(new SupervisorCommand())->run($input, $output);