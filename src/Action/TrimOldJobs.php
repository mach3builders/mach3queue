<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class TrimOldJobs
{
    public function __invoke(array $config): void
    {
        $completed_seconds = $config['trim']['completed'];
        $failed_seconds = $config['trim']['failed'];

        Job::olderThanSeconds($completed_seconds)->completed()->delete();
        Job::olderThanSeconds($failed_seconds)->failed()->delete();
    }
}