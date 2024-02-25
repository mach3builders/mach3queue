<?php

namespace Mach3queue\Console;

use Mach3queue\Action\PrepareTables;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Installing queue.');

        (new PrepareTables)->execute();
        $this->copyConfigFile();

        $output->writeln('Done installing.');

        return Command::SUCCESS;
    }

    private function copyConfigFile(): void
    {
        $source = __DIR__.'/../../../queue.php';
        $destination = __DIR__.'/../../../queue.php';

        if (file_exists($destination)) {
            return;
        }

        copy($source, $destination);
    }
}