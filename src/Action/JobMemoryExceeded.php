<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class JobMemoryExceeded
{
    public function __invoke(?Job $job, ?int $time_to_retry): void
    {
        if (empty($job)) {
            return;
        }

        (new BuryJob)($job, Job::$memory_exceeded_message, $time_to_retry);
    }
}