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
        $input = new ArgvInput($this->params($argv));
        $output = new ConsoleOutput;

        match ($this->method($argv)) {
            null => (new QueueCommand($this->config))->run($input, $output),
            'worker' => (new WorkerCommand())->run($input, $output),
            'supervisor' => (new SupervisorCommand())->run($input, $output),
            'install' => (new InstallCommand)->run($input, $output),
            'terminate' => (new TerminateCommand())->run($input, $output),
            'restart' => (new RestartCommand())->run($input, $output),
            default => null,
        };
    }

    private function method(array $argv): string|null
    {
        return $argv[1] ?? null;
    }

    private function params(array $argv): array
    {
        return array_slice($argv, 2);
    }
}
