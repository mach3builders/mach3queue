<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class TrimOldJobs
{
    public function __invoke(array $config): void
    {
        $completed_minutes = $config['trim']['completed'];
        $failed_minutes = $config['trim']['failed'];

        Job::olderThanSeconds($completed_minutes * 60)->completed()->delete();
        Job::olderThanSeconds($failed_minutes * 60)->failed()->delete();
    }
}