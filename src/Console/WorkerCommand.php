<?php

namespace Mach3queue\Console;

use Mach3queue\Queue\Queue;
use Mach3queue\Worker\Worker;
use Mach3queue\Worker\WorkerActions;
use Mach3queue\Worker\WorkerOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends Command
{
    public function __construct(private array $config)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption('stop-when-empty', 's'),
                new InputOption('queue', 'q', InputOption::VALUE_REQUIRED),
                new InputOption('timeout', 't', InputOption::VALUE_OPTIONAL),
            ])
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $worker_params = $this->workerParams($input);
        $worker = new Worker(...$worker_params);
        $worker->run();

        return Command::SUCCESS;
    }

    private function workerParams(InputInterface $input): array
    {
        $queue_name = $input->getOption('queue') ?? Queue::DEFAULT_QUEUE;
        $queue = new Queue;
        $queue->setConnection([
            'driver' => $this->config['db_driver'] ?? 'mysql',
            'host' => $this->config['db_host'],
            'database' => $this->config['db_name'],
            'username' => $this->config['db_user'],
            'password' => $this->config['db_pass'],
        ]);

        return [
            'queue' => $queue->on($queue_name),
            'timeout' => $input->getOption('timeout') ?? 60,
            'actions' => new WorkerActions,
            'options' => new WorkerOptions(
                stop_when_empty: $input->getOption('stop-when-empty'),
            ),
        ];
    }
}
