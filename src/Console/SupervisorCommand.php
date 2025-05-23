<?php

namespace Mach3queue\Console;

use Mach3queue\Supervisor\Supervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Symfony\Component\Console\Command\Command;
use Mach3queue\Action\GetQueueNamesFromConsole;
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
                new InputOption('max-processes', 'max', InputOption::VALUE_REQUIRED),
                new InputOption('min-processes', 'min', InputOption::VALUE_REQUIRED),
                new InputOption('name', 'n', InputOption::VALUE_REQUIRED),
                new InputOption('queue', 'q', InputOption::VALUE_REQUIRED),
                new InputOption('master', 'm', InputOption::VALUE_REQUIRED),
                new InputOption('timeout', 't', InputOption::VALUE_OPTIONAL),
                new InputOption('directory', 'd', InputOption::VALUE_OPTIONAL),
                new InputOption('max-retries', 'mr', InputOption::VALUE_OPTIONAL),
                new InputOption('time-to-retry', 'ttr', InputOption::VALUE_OPTIONAL),
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
            name: $input->getOption('name'),
            master: $input->getOption('master'),
            queues: GetQueueNamesFromConsole::get($input),
            timeout: $input->getOption('timeout'),
            maxProcesses: $input->getOption('max-processes'),
            minProcesses: $input->getOption('min-processes'),
            directory: $input->getOption('directory') ?? __DIR__,
            maxRetries: $input->getOption('max-retries'),
            timeToRetry: $input->getOption('time-to-retry'),
        );
    }
}