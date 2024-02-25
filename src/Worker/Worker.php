<?php

namespace Mach3queue\Worker;

use Mach3queue\Job\Job;
use Mach3queue\Queue\Queue;

class Worker
{
    const int EXIT_ERROR = 1;
    const int EXIT_MEMORY_LIMIT = 12;

    private bool $should_quit = false;

    public function __construct(
        private readonly Queue $queue,
        private readonly int $timeout = 60,
        private readonly WorkerActions $actions = new WorkerActions,
        private readonly WorkerOptions $options = new WorkerOptions,
    ) {
    }

    public function run(): int
    {
        $this->listenForSignalsOnWorker();

        while(true) {
            $job = $this->queue->getNextJob();

            if ($stop = $this->checkIfShouldStop($job)) {
                return $stop;
            }
            
            if(empty($job)) {
                sleep(1);
                continue;
            }

            $this->registerTimeoutHandlerForJob(function() use ($job) {
                $this->actions->timeoutJob($job);
                $this->actions->killWorker();
            });
            
            $this->runJob($job);

            $this->resetTimeoutHandler();
        }
    }

    public function listenForSignalsOnWorker(): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGQUIT, fn() => $this->terminate());
        pcntl_signal(SIGTERM, fn() => $this->terminate());
        pcntl_signal(SIGUSR2, fn() => $this->pause());
        pcntl_signal(SIGCONT, fn() => $this->resume());
    }

    public function registerTimeoutHandlerForJob(callable $callback): void
    {
        pcntl_signal(SIGALRM, $callback);
        pcntl_alarm(max($this->timeout, 0));
    }

    public function resetTimeoutHandler(): void
    {
        pcntl_alarm(0);
    }

    public function terminate(): void
    {
        echo "\033[34mQuitting worker\033[0m".PHP_EOL;

        $this->should_quit = true;
    }

    public function pause(): void
    {
    }

    public function resume(): void
    {
    }

    private function checkIfShouldStop(?Job $job = null): int
    {
        return match (true) {
            $this->memoryExceeded() => self::EXIT_MEMORY_LIMIT,
            $this->should_quit, $this->options->stop_when_empty && empty($job) => self::EXIT_ERROR,
            default => 0,
        };
    }

    private function memoryExceeded(): bool
    {
        $memory = $this->options->memory;
        return (memory_get_usage(true) / 1024 / 1024) >= $memory;
    }

    private function runJob(Job $job): void
    {
        try {
            $this->actions->runJob($job);
            $this->actions->completeJob($job);
        } catch(\Exception $e) {
            $this->actions->buryJob($job, $e->getMessage());
        }
    }
}