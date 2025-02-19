<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class TimeoutJob
{
    public function __invoke(Job $job, int $time_to_retry): void
    {
        (new BuryJob)($job, Job::$timeout_message, $time_to_retry);
    }
}