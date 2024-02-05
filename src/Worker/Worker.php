<?php

namespace Mach3queue\Worker;

use Mach3queue\Job\Job;
use Mach3queue\Queue\Queue;
use Mach3queue\Worker\AsyncSignal;

class Worker
{
    const EXIT_ERROR = 1;

    private bool $shouldQuit = false;

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

            $timeout_handler = $this->timeoutHandler($job);
            $async_signal->registerTimeoutHandlerForJob($timeout_handler);
            
            $this->runJob($job);

            $async_signal->resetTimeoutHandler();
        }
    }

    public function timeoutHandler(Job $job): callable
    {
        return function() use ($job) {
            $this->actions->timeoutJob($job);
            $this->actions->killWorker();
        };
    }

    public function quit(): void
    {
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
            $this->shouldQuit => self::EXIT_ERROR,
            $this->options->stop_when_empty && empty($job) => self::EXIT_ERROR,
            default => 0,
        };
    }

    private function runJob(Job $job): void
    {
        try {
            $this->actions->runJob($job);
        } catch(\Exception $e) {
            $this->actions->buryJob($job->id, $e->getMessage());
        }
    }
}