<?php
namespace Mach3queue\Queue;

use Mach3queue\Action\AddJob;
use Mach3queue\Action\ReserveJob;
use Mach3queue\Action\SetupDatabase;
use Mach3queue\Job\Job;

class Queue
{
    const DEFAULT_QUEUE = 'default';
    private string $queue = self::DEFAULT_QUEUE;

    public function setConnection(array $config): void
    {
        (new SetupDatabase)->execute($config);
    }

    public function on(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function addJob(
        Queueable $job,
        int $delay = 0,
        int $priority = 1024,
        int $time_to_retry = 60
    ): Job {
        $new_job = (new AddJob)->execute(
            queue: $this->queue,
            payload: serialize($job),
            delay: $delay,
            priority: $priority,
            time_to_retry: $time_to_retry,
        );

        $this->resetQueue();

        return $new_job;
    }

    public function deleteJob(int $id): void
    {
        Job::where('id', $id)->delete();
	}

    public function getNextJob(): ?Job
    {
        $job = Job::nextJobForQueue($this->queue)->first();

        if ($job) {
            (new ReserveJob)->execute(job: $job);
        }

        $this->resetQueue();
        
        return $job;
	}

    private function resetQueue(): void
    {
        $this->queue = self::DEFAULT_QUEUE;
    }
}
