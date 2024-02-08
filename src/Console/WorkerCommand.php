<?php

namespace Mach3queue\Console;

use Mach3queue\Queue\Queue;
use Mach3queue\Worker\Worker;
use Mach3queue\Worker\WorkerActions;
use Mach3queue\Worker\WorkerOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends Command
{
    public function __construct(private array $config)
    {
        parent::__construct('Worker Command');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $queue = new Queue;
        $queue->setConnection([
            'driver' => $this->config['db_driver'] ?? 'mysql',
            'host' => $this->config['db_host'],
            'database' => $this->config['db_name'],
            'username' => $this->config['db_user'],
            'password' => $this->config['db_pass'],
        ]);
        $worker_params = $this->workerParams($input);
        $worker = new Worker(...$worker_params);
        $worker->run();

        return Command::SUCCESS;
    }

    private function workerParams(InputInterface $input): array
    {
        return [
            'queue' => $input->getOption('queue') ?? Queue::DEFAULT_QUEUE,
            'timeout' => $input->getOption('timeout') ?? 60,
            'actions' => new WorkerActions,
            'options' => new WorkerOptions(
                stop_when_empty: $input->getOption('stop-when-empty'),
            ),
        ];
    }
}