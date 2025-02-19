<?php
namespace Mach3queue\Queue;

use Mach3queue\Action\SetupDatabase;
use Mach3queue\Job\Job;
use Illuminate\Database\Capsule\Manager as Database;

class Queue
{
    public static string $default_queue = 'default';
    private string $queue = 'default';
    private array $pipelines = [];
    private QueueActions $actions;
    public int $maxRetries = 3;
    public int $timeout = 60;

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

    public function pipelines(array $pipelines): static
    {
        $this->pipelines = $pipelines;

        return $this;
    }

    public function setMaxRetries(int $retries): static
    {
        $this->maxRetries = $retries;

        return $this;
    }

    public function setTimeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function addJob(
        Queueable $job,
        int $delay = 0,
        int $priority = 1024
    ): Job {
        $new_job = $this->actions->addJob(
            queue: $this->queue,
            payload: serialize($job),
            delay: $delay,
            priority: $priority,
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
            $job = Job::nextJobForPipeLines(
                    $this->getPipelines(),
                    $this->maxRetries,
                    $this->timeout,
                )
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
        $this->queue = self::$default_queue;
    }
}
