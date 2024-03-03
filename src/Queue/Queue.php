<?php
namespace Mach3queue\Queue;

use Mach3queue\Action\SetupDatabase;
use Mach3queue\Job\Job;
use Illuminate\Database\Capsule\Manager as Database;

class Queue
{
    const string DEFAULT_QUEUE = 'default';

    private string $queue = self::DEFAULT_QUEUE;

    private array $pipelines = [];

    private QueueActions $actions;

    public function __construct(QueueActions $actions = new QueueActions)
    {
        $this->actions = $actions;
    }

    public function setConnection(array $config): void
    {
        (new SetupDatabase)($config);
    }

    public function on(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function  pipelines(array $pipelines): static
    {
        $this->pipelines = $pipelines;

        return $this;
    }

    public function addJob(
        Queueable $job,
        int $delay = 0,
        int $priority = 1024,
        int $time_to_retry = 60
    ): Job {
        $new_job = $this->actions->addJob(
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
        $job = null;
        Database::beginTransaction();

        try {
            $job = Job::nextJobForPipeLines($this->getPipelines())
                ->lockForUpdate()
                ->first();

            if ($job) {
                $this->actions->reserveJob($job);
            }

            Database::commit();
        } catch (\Throwable $_) {
            Database::rollBack();
        }

        $this->resetQueue();

        return $job;
	}

    public function getTotalJobsInQueue(): int
    {
        return Job::nextJobForPipeLines($this->getPipelines())->count();
    }

    private function getPipelines(): array
    {
        return $this->pipelines ?: [$this->queue];
    }

    private function resetQueue(): void
    {
        $this->queue = self::DEFAULT_QUEUE;
    }
}
