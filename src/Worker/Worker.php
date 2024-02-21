<?php

namespace Mach3queue\Worker;

use Mach3queue\Job\Job;
use Mach3queue\Queue\Queue;
use Mach3queue\Worker\AsyncSignal;

class Worker
{
    const EXIT_ERROR = 1;

    private bool $should_quit = false;

    public function __construct(
        private Queue $queue, 
        private int $timeout = 60,
        private WorkerActions $actions = new WorkerActions,
        private WorkerOptions $options = new WorkerOptions,
    ) {
    }

    public function run(): int
    {
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
                $this->actions->timeoutJob->execute($job);
                $this->actions->killWorker->execute();
            });
            
            $this->runJob($job);

            $this->resetTimeoutHandler();
        }
    }

    public function listenForSignalsOnWorker(Worker $worker): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGQUIT, fn() => $worker->terminate());
        pcntl_signal(SIGTERM, fn() => $worker->terminate());
        pcntl_signal(SIGUSR2, fn() => $worker->pause());
        pcntl_signal(SIGCONT, fn() => $worker->resume());
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
            $this->should_quit => self::EXIT_ERROR,
            $this->options->stop_when_empty && empty($job) => self::EXIT_ERROR,
            default => 0,
        };
    }

    private function runJob(Job $job): void
    {
        try {
            $this->actions->runJob->execute($job);
            $this->actions->completeJob->execute($job);
        } catch(\Exception $e) {
            $this->actions->buryJob->execute($job, $e->getMessage());
        }
    }
}