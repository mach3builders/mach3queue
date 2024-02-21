<?php

use Mach3queue\Action\PrepareTables;
use Mach3queue\Console\WorkerCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/../vendor/autoload.php';

$config = [
    'driver' => 'sqlite',
    'host' => 'localhost',
    'database' => ':memory:',
    'username' => 'test',
    'password' => 'test',
];
$input = new ArgvInput($argv);
$output = new ConsoleOutput;

(new PrepareTables)->execute();
(new WorkerCommand($config))->run($input, $output);