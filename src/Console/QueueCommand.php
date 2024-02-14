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
        $this->createNewMaster();
        $this->listenForInterruption();

        $output->writeln('Queue started successfully.');

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

    private function createNewMaster(): void
    {
        $this->master = new MasterSupervisor($this->config['queue']);
        $this->master->handleOutputUsing(function($_, $line) {
            $this->output->writeln($line);
        });
    }

    private function listenForInterruption(): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGINT, fn() => $this->shutDown());
    }

    private function shutDown(): void
    {
        $this->output->writeln('');
        $this->output->writeln('Shutting down...');
        $this->master->terminate();
    }
}