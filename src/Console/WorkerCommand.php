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
    private array $database;

    public function __construct(array $config)
    {
        $this->database = $config['database'];

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
        
        return $worker->run();
    }

    private function workerParams(InputInterface $input): array
    {
        return [
            'queue' => $this->createQueue($input),
            'timeout' => $input->getOption('timeout') ?? 60,
            'actions' => new WorkerActions,
            'options' => new WorkerOptions(
                stop_when_empty: $input->getOption('stop-when-empty'),
            ),
        ];
    }

    private function createQueue(InputInterface $input): Queue
    {
        $queue_names = $this->getQueueNames($input);
        
        $queue = new Queue;
        $queue->pipelines($queue_names);
        $queue->setConnection([
            'driver' => $this->database['driver'] ?? 'mysql',
            'host' => $this->database['host'],
            'database' => $this->database['database'],
            'username' => $this->database['username'],
            'password' => $this->database['password'],
        ]);

        return $queue;
    }

    private function getQueueNames(InputInterface $input): array
    {
        $queue = $input->getOption('queue');

        return $queue ? explode(',', $queue) : [Queue::DEFAULT_QUEUE];
    }
}
