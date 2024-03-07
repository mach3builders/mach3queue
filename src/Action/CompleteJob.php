<?php

namespace Mach3queue\Action;

use Mach3queue\Stopwatch;
use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class CompleteJob
{
    private Job $job;
    private mixed $payload;

    public function __invoke(Job $job): void
    {
        $this->job = $job;
        $this->payload = unserialize($job->payload);

        $this->completeAction();
        $this->printFinishedJob();
    }

    private function completeAction(): void
    {
        $this->job->is_complete = true;
        $this->job->complete_dt = Carbon::now();
        $this->job->runtime = Stopwatch::check($this->job->id);
        $this->job->save();

        Stopwatch::forget($this->job->id);
    }

    private function printFinishedJob(): void
    {
        $pid = getmypid();
        $id = $this->job->id;
        $class = get_class($this->payload);
        $time = date('Y-m-d H:i:s');

        echo "\033[32m$time [$pid] finished job: [$id] $class\033[0m".PHP_EOL;
    }
}
