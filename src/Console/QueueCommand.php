<?php

namespace Mach3queue\Console;

use Mach3queue\Supervisor\MasterSupervisor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueCommand extends Command
{
    private OutputInterface $output;
    
    private MasterSupervisor $master;

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->validateConfig();
        $this->createNewMaster($output);
        $this->listenForInterruption();

        $output->writeln('<info>Queue started successfully.</info>');

        $this->master->monitor();

        return Command::SUCCESS;
    }

    private function validateConfig(): void
    {
        if (isset($this->config['queue'])) {
            return;
        }

        throw new \Exception('Queue configuration is missing.');
    }

    private function createNewMaster(OutputInterface $output): void
    {
        $callable = fn($_, $line) => $output->write($line);
        $this->master = new MasterSupervisor($this->config['queue']);
        $this->master->handleOutputUsing($callable);
    }

    private function listenForInterruption(): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGINT, fn() => $this->shutDown());
    }

    private function shutDown(): void
    {
        $this->output->writeln('');
        $this->output->writeln('\033[34Shutting down...\033[0');
        $this->master->terminate();
    }
}