<?php

namespace Mach3queue\Supervisor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SupervisorCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = $this->getSupervisorOptions();
        $supervisor = new Supervisor($options);
        $supervisor->handleOutputUsing(fn($_, $line) => $output->writeln($line));
        $supervisor->monitor();

        return Command::SUCCESS;
    }

    private function getSupervisorOptions(): SupervisorOptions
    {
        return new SupervisorOptions();
    }
}