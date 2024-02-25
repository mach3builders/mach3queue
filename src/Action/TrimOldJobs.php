<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class TrimOldJobs
{
    public function __invoke(): void
    {
        Job::whereIsOld()->whereIsDone()->delete();
    }
}