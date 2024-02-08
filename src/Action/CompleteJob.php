<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class CompleteJob
{
    public function execute(Job $job): void
    {
        $job->is_complete = true;
        $job->complete_dt = Carbon::now();
        $job->save();
    }
}