<?php

namespace Mach3queue\Action;

use Mach3queue\Stopwatch;
use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;
use function Opis\Closure\{unserialize};

class BuryJob
{
    private Job $job;
    private mixed $payload;
    private string $message;

    private int $time_to_retry;

    public function __invoke(Job $job, string $message, int $time_to_retry): void
    {
        $this->job = $job;
        $this->payload = unserialize($job->payload);
        $this->message = $message;
        $this->time_to_retry = $time_to_retry;
        $after = $job->callback ? unserialize($job->callback) : null;

        $this->buryJob();
        $this->echoBuriedJob();

        if (is_callable($after)) {
            ($after)($this->job);
        }
    }

    private function buryJob(): void
    {
        $this->job->is_buried = 1;
        $this->job->buried_dt = Carbon::now();
        $this->job->is_reserved = 0;
        $this->job->reserved_dt = null;
        $this->job->message = $this->message;
        $this->job->runtime = Stopwatch::check($this->job->id);
        $this->job->time_to_retry_dt = Carbon::now()->addSeconds($this->time_to_retry);
        $this->job->save();

        Stopwatch::forget($this->job->id);
    }

    private function echoBuriedJob(): void
    {
        $pid = getmypid();
        $id = $this->job->id;
        $class = get_class($this->payload);
        $time = date('Y-m-d H:i:s');

        echo "\033[31m$time [$pid] buried   job: [$id] $class\033[0m".PHP_EOL;
        echo "\033[31m$time [$pid] message: $this->message\033[0m".PHP_EOL;
    }
}
