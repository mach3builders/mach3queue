<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class JobMemoryExceeded
{
    public function __invoke(?Job $job): void
    {
        if (empty($job)) {
            return;
        }

        (new BuryJob)($job, Job::MEMORY_EXCEEDED_MESSAGE);
    }
}