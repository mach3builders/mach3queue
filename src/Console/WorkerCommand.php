<?php

namespace Mach3queue\Console;

use Mach3queue\Queue\Queue;
use Mach3queue\Worker\Worker;
use Mach3queue\Queue\QueueManager;
use Mach3queue\Worker\WorkerActions;
use Mach3queue\Worker\WorkerOptions;
use Symfony\Component\Console\Command\Command;
use Mach3queue\Action\GetQueueNamesFromConsole;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption('stop-when-empty', 's'),
                new InputOption('queue', 'q', InputOption::VALUE_REQUIRED),
                new InputOption('timeout', 't', InputOption::VALUE_OPTIONAL),
                new InputOption('memory', 'm', InputOption::VALUE_OPTIONAL),
                new InputOption('max-retries', 'mr', InputOption::VALUE_OPTIONAL),
                new InputOption('time-to-retry', 'ttr', InputOption::VALUE_OPTIONAL),
            ])
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return (new Worker(...$this->workerParams($input)))->run();
    }

    private function workerParams(InputInterface $input): array
    {
        return [
            'queue' => $this->createQueue($input),
            'timeout' => $input->getOption('timeout') ?? 60,
            'actions' => new WorkerActions,
            'options' => new WorkerOptions(
                stop_when_empty: $input->getOption('stop-when-empty'),
                memory: $input->getOption('memory') ?? 128,
            ),
            'maxRetries' => $input->getOption('max-retries') ?? 3,
            'timeToRetry' => $input->getOption('time-to-retry') ?? 60,
        ];
    }

    private function createQueue(InputInterface $input): Queue
    {
        $queues = GetQueueNamesFromConsole::get($input);

        return QueueManager::manager()->getInstance()->pipelines($queues);
    }
}
