<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class TimeoutJob
{
    public function __invoke(Job $job): void
    {
        (new BuryJob)($job, Job::TIMEOUT_MESSAGE);
    }
}