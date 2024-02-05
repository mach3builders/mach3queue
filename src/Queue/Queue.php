<?php
namespace Mach3queue\Queue;

use Illuminate\Support\Carbon;
use Mach3queue\Action\SetupDatabase;
use Mach3queue\Job\Job;

class Queue
{
    const DEFAULT_QUEUE = 'default';
    private string $queue = self::DEFAULT_QUEUE;

    public function setConnection(array $config): void
    {
        (new SetupDatabase)->execute(...$config);
    }

    public function on(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function get(int $id): Job
    {
        return Job::find($id);
    }

    public function poll($job_id)
    {
        return Job::find($job_id)->status();
    }

    public function addJob(
        Queueable $job,
        int $delay = 0,
        int $priority = 1024,
        int $time_to_retry = 60
    ): int {
        $entry = new Job;
        $entry->queue = $this->queue;
        $entry->payload = serialize($job);
        $entry->added_dt = Carbon::now();
        $entry->send_dt = Carbon::now()->addSeconds($delay);
        $entry->priority = $priority;
        $entry->is_reserved = 0;
        $entry->reserved_dt = null;
        $entry->is_buried = 0;
        $entry->attempts = 0;
        $entry->time_to_retry_dt = Carbon::now()->addSeconds($time_to_retry);
        $entry->is_complete = 0;
        $entry->save();

        $this->resetQueue();

        return $entry->id;
    }

    public function deleteJob(int $id): void
    {
        Job::where('id', $id)->delete();
	}

    public function buryJob(int $id, string $message): void
    {
        $job = Job::find($id);
        $job->is_buried = 1;
        $job->buried_dt = Carbon::now();
        $job->is_reserved = 0;
        $job->reserved_dt = null;
        $job->message = $message;
        $job->save();
	}

    public function getNextJob(): ?Job
    {
        $job = Job::where('queue', $this->queue)
            ->where('send_dt', '<=', Carbon::now())
            ->where('is_buried', 0)
            ->where('is_complete', 0)
            ->where(function ($query) {
                $query->where('is_reserved', 0)
                    ->orWhere(function ($query) {
                        $query->where('is_reserved', 1)
                            ->where('reserved_dt', '<=', Carbon::now()->subMinutes(5));
                    });
            })
            ->where(function ($query) {
                $query->where('attempts', 0)
                    ->orWhere(function ($query) {
                        $query->where('attempts', '>=', 1)
                            ->where('time_to_retry_dt', '<=', Carbon::now());
                    });
            })
            ->orderBy('priority')
            ->first();

        if ($job) {
            $job->is_reserved = 1;
            $job->reserved_dt = Carbon::now();
            $job->attempts = $job->attempts + 1;
            $job->save();
        }

        $this->resetQueue();
        
        return $job;
	}

    private function resetQueue(): void
    {
        $this->queue = self::DEFAULT_QUEUE;
    }
}
