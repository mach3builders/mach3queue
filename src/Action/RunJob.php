<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class RunJob
{
    public function execute(Job $job): void
    {
        $action = unserialize($job->payload);
        $action->handle();
    }
}