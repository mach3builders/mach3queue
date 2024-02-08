<?php

namespace Mach3queue\Console;

use Mach3queue\Queue\QueueManager as Queue;
use Mach3queue\Worker\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    public function __construct(private array $config)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        QueueManager
        return Command::SUCCESS;
    }
}
