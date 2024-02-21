<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class CompleteJob
{
    private Job $job;
    private mixed $action;

    public function execute(Job $job): void
    {
        $this->job = $job;
        $this->action = unserialize($job->payload);

        $this->completeAction();
        $this->printDone();
    }

    private function completeAction(): void
    {
        $this->job->is_complete = true;
        $this->job->complete_dt = Carbon::now();
        $this->job->save();
    }

    private function printDone(): void
    {
        $pid = getmypid();
        $id = $this->job->id;
        $class = get_class($this->action);
        $time = date('Y-m-d H:i:s');

        echo "\033$time [32m[$pid] finished job: [$id] $class\033[0m".PHP_EOL;
    }
}
