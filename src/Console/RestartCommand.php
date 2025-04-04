<?php

namespace Mach3queue\Console;

use Symfony\Component\Console\Command\Command;
use Mach3queue\Supervisor\SupervisorRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Restarting all supervisors.');

        $masters = SupervisorRepository::allMasters();

        if (! count($masters)) {
            $output->writeln('<info>No queue masters found.</info>');

            return Command::SUCCESS;
        }

        foreach ($masters as $master) {
            $this->restartMaster($master, $output);
        }

        return Command::SUCCESS;
    }

    private function restartMaster(mixed $master, OutputInterface $output): void
    {
        $output->writeln("<info>Sending USR1 signal to process $master->pid.</info>");

        if(posix_kill($master->pid, SIGUSR1)) {
            return;
        }

        $error = posix_strerror(posix_get_last_error());

        $output->writeln("<error>Failed to terminate master $master->pid ($error).</error>");
    }
}
