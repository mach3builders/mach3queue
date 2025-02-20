<?php

namespace Mach3queue\Worker;

use Mach3queue\Job\Job;
use Mach3queue\Queue\Queue;

class Worker
{
    public static int $EXIT_ERROR = 1;
    public static int $EXIT_MEMORY_LIMIT = 12;
    public bool $should_quit = false;
    public bool $working = true;

    public function __construct(
        private readonly Queue $queue,
        private readonly int $timeout = 60,
        private readonly WorkerActions $actions = new WorkerActions,
        private readonly WorkerOptions $options = new WorkerOptions,
        private readonly int $timeToRetry = 60
    ) {
    }

    public function run(): int
    {
        $this->listenForSignalsOnWorker();

        while(true) {
            if(! $this->working) {
                sleep(1);
                continue;
            }

            if ($this->should_quit) {
                return self::$EXIT_ERROR;
            }

            $job = $this->queue->getNextJob();

            if ($stop = $this->checkIfShouldStop($job)) {
                $this->handleStop($stop, $job);
                return $stop;
            }
            
            if(empty($job)) {
                sleep(1);
                continue;
            }

            $this->registerTimeoutHandlerForJob(function() use ($job) {
                $this->actions->timeoutJob($job, $this->timeToRetry);
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
        echo "\033[34mPausing worker\033[0m".PHP_EOL;

        $this->working = false;
    }

    public function resume(): void
    {
        echo "\033[34mResuming worker\033[0m".PHP_EOL;

        $this->working = true;
    }

    private function checkIfShouldStop(?Job $job = null): int
    {
        return match (true) {
            $this->memoryExceeded() => self::$EXIT_MEMORY_LIMIT,
            $this->stopWhenEmpty($job),
            $this->should_quit => self::$EXIT_ERROR,
            default => 0,
        };
    }

    private function handleStop(int $stop, ?Job $job): void
    {
        match ($stop) {
            self::$EXIT_ERROR,
            self::$EXIT_MEMORY_LIMIT => $this->actions->jobMemoryExceeded(
                $job,
                $this->timeToRetry
            ),
        };
    }

    private function memoryExceeded(): bool
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $this->options->memory;
    }

    private function runJob(Job $job): void
    {
        try {
            $this->actions->runJob($job);
            $this->actions->completeJob($job);
        } catch(\Throwable $e) {
            $this->actions->buryJob($job, $e->getMessage(), $this->timeToRetry);
        }
    }

    private function stopWhenEmpty(?Job $job): bool
    {
        return $this->options->stop_when_empty && empty($job);
    }
}