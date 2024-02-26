<?php

namespace Mach3queue\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Restarting all supervisors.');

        return Command::SUCCESS;
    }
}