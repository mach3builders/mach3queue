<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class TimeoutJob
{
    public function execute(Job $job): void
    {
        // $job->timedOut();
    }
}