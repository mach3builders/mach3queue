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
            'queue' => (new QueueCommand($this->config))->run($input, $output),
            'queue:worker' => (new WorkerCommand($this->config))->run($input, $output),
            'queue:supervisor' => (new SupervisorCommand)->run($input, $output),
            'queue:install' => (new InstallCommand)->run($input, $output),
            'queue:terminate' => (new TerminateCommand())->run($input, $output),
            default => null,
        };
    }
}
