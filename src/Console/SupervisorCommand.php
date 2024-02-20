<?php

namespace Mach3queue\Console;

use Mach3queue\Queue\Queue;
use Mach3queue\SuperVisor\Supervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SupervisorCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption('max-processes', 'mp', InputOption::VALUE_REQUIRED),
                new InputOption('queue', 'q', InputOption::VALUE_REQUIRED),
                new InputOption('master', 'm', InputOption::VALUE_REQUIRED),
                new InputOption('timeout', 't', InputOption::VALUE_OPTIONAL),
                new InputOption('directory', 'd', InputOption::VALUE_OPTIONAL),
            ])
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = $this->getSupervisorOptions($input);
        $supervisor = new Supervisor($options);
        $supervisor->handleOutputUsing(fn($_, $line) => $output->write($line));
        $supervisor->scale($options->maxProcesses);
        $supervisor->monitor();

        return Command::SUCCESS;
    }

    private function getSupervisorOptions(InputInterface $input): SupervisorOptions
    {
        return new SupervisorOptions(
            master: $input->getOption('master'),
            queues: $this->getQueueNames($input),
            maxProcesses: $input->getOption('max-processes'),
            directory: $input->getOption('directory') ?? __DIR__,
        );
    }

    private function getQueueNames(InputInterface $input): array
    {
        $queue = $input->getOption('queue');

        return $queue ? explode(',', $queue) : [Queue::DEFAULT_QUEUE];
    }
}