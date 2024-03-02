<?php

namespace Mach3queue\Console;

use Exception;
use Mach3queue\Supervisor\MasterSupervisor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueCommand extends Command
{
    private MasterSupervisor $master;
    private array $config;
    private bool $monitor;

    public function __construct(array $config, bool $monitor = true)
    {
        $this->config = $config;
        $this->monitor = $monitor;

        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createNewMaster($output);
        $this->listenForInterruption($output);

        $output->writeln('<info>Queue started successfully.</info>');

        if ($this->monitor) {
            $this->master->monitor();
        } else {
            $this->master->persist();
        }

        return Command::SUCCESS;
    }

    private function createNewMaster(OutputInterface $output): void
    {
        $callable = fn($_, $line) => $output->write($line);
        $this->master = new MasterSupervisor($this->config);

        $this->master->handleOutputUsing($callable);
    }

    private function listenForInterruption(OutputInterface $output): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGINT, fn() => $this->shutDown($output));
    }

    private function shutDown(OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>Shutting down...</info>');

        $this->master->terminate();
    }
}