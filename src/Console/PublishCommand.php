<?php

namespace Mach3queue\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Publishing the config file.');

        $this->copyConfigFile($output);

        return Command::SUCCESS;
    }

    private function copyConfigFile(OutputInterface $output): void
    {
        $source = __DIR__.'../../config/queue.php';
        $destination = __DIR__.'/../../../../../queue.php';

        if (!file_exists($destination)) {
            copy($source, $destination);
            $output->writeln('Done publishing.');
        } else {
            $output->writeln('Config file already exists.');
        }
    }
}