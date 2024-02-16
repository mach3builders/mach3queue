<?php

namespace Mach3queue\Console;

use Mach3queue\Action\PrepareTables;
use Mach3queue\Supervisor\MasterSupervisor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Installing queue.');

        (new PrepareTables)->execute($this->config);

        $output->writeln('Done installing.');

        return Command::SUCCESS;
    }
}