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
        $async_signal = new AsyncSignal;
        $async_signal->setTimeout($this->timeout);

        while(true) {
            $job = $this->queue->getNextJob();

            if ($stop = $this->checkIfShouldStop($job)) {
                return $stop;
            }
            
            if(empty($job)) {
                sleep(1);
                continue;
            }

            $async_signal->registerTimeoutHandlerForJob(function() use ($job) {
                $this->actions->timeoutJob->execute($job);
                $this->actions->killWorker->execute();
            });
            
            $this->runJob($job);

            $async_signal->resetTimeoutHandler();
        }
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