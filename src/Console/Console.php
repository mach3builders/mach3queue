<?php

namespace Mach3queue\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Console
{
    public function __construct(private array $config)
    {
    }

    public function handle(array $argv)
    {
        $params = $argv;
        unset($params[1]);
        $input = new ArgvInput($params);
        $output = new ConsoleOutput;

        match ($argv[1]) {
            'install' => (new InstallCommand($this->config))->run($input, $output),
            'worker' => (new WorkerCommand($this->config))->run($input, $output),
            default => null,
        };
    }
}
