<?php

namespace Mach3queue\Console;

use Mach3queue\Supervisor\SupervisorRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TerminateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Terminating queue.</info>');

        $masters = SupervisorRepository::allMasters();

        foreach ($masters as $master) {
            $output->writeln("<info>Sending TERM signal to process {$master->pid}.</info>");

            $result = posix_kill($master->pid, SIGTERM);

            if (!$result) {
                $error = posix_strerror(posix_get_last_error());
                
                $output->writeln("<error>Failed to terminate master {$master->pid} ({$error}).</error>");
            }
        }

        return Command::SUCCESS;
    }
}