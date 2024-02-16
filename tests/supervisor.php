<?php

use Mach3queue\Console\SupervisorCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__.'/../vendor/autoload.php';

new SupervisorCommand();

$input = new ArgvInput($argv);
$output = new ConsoleOutput;

(new SupervisorCommand())->run($input, $output);