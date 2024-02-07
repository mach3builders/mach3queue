<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class ReserveJob
{
    public function execute(Job $job): void
    {
        $job->is_reserved = 1;
        $job->reserved_dt = Carbon::now();
        $job->attempts = $job->attempts + 1;
        $job->save();
    }
}