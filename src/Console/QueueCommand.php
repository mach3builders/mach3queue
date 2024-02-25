<?php

namespace Mach3queue\Console;

use Exception;
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

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->createNewMaster($output);
        $this->listenForInterruption();

        $output->writeln('<info>Queue started successfully.</info>');

        $this->master->monitor();

        return Command::SUCCESS;
    }

    private function createNewMaster(OutputInterface $output): void
    {
        $callable = fn($_, $line) => $output->write($line);
        $this->master = new MasterSupervisor($this->config);
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
        $this->output->writeln('<info>Shutting down...</info>');
        $this->master->terminate();
    }
}