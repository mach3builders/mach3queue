<?php

namespace Mach3queue\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Console
{
    public function __construct(
        private array $config,
        private array $args
    ) {
    }

    public function handle(ArgvInput $input, ConsoleOutput $output)
    {
        match ($this->args[1]) {
            'app:worker' => (new WorkerCommand($this->config))->execute($input, $output),
            default => null,
        };     
    }
}