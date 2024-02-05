<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class BuryJob
{
    public function execute(Job $job, string $message): void
    {
        $job->is_buried = 1;
        $job->buried_dt = Carbon::now();
        $job->is_reserved = 0;
        $job->reserved_dt = null;
        $job->message = $message;
        $job->save();
    }
}