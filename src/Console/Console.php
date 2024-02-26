<?php

namespace Mach3queue\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Exception\ExceptionInterface;

readonly class Console
{
    public function __construct(private array $config)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function handle(array $argv): void
    {
        $params = $argv;
        unset($params[1]);
        $input = new ArgvInput($params);
        $output = new ConsoleOutput;

        match ($argv[1] ?? null) {
            null => (new QueueCommand($this->config))->run($input, $output),
            'worker' => (new WorkerCommand($this->config))->run($input, $output),
            'supervisor' => (new SupervisorCommand($this->config))->run($input, $output),
            'install' => (new InstallCommand)->run($input, $output),
            'terminate' => (new TerminateCommand())->run($input, $output),
            'restart' => (new RestartCommand())->run($input, $output),
            default => null,
        };
    }
}
