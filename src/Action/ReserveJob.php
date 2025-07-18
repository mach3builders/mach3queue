<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class ReserveJob
{
    public function __invoke(Job $job): void
    {
        $job->is_reserved = 1;
        $job->reserved_dt = Carbon::now();
        ++$job->attempts;
        $job->save();
    }
}