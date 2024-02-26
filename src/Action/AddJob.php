<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class AddJob
{
    public function __invoke(
        string $queue,
        string $payload,
        int $delay,
        int $priority,
        int $time_to_retry
    ): Job {
        $job = new Job;
        $job->queue = $queue;
        $job->payload = $payload;
        $job->added_dt = Carbon::now();
        $job->send_dt = Carbon::now()->addSeconds($delay);
        $job->priority = $priority;
        $job->time_to_retry_dt = Carbon::now()->addSeconds($time_to_retry);
        $job->save();

        return $job;
    }
}