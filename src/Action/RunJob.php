<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class RunJob
{
    private Job $job;
    private mixed $action;

    public function execute(Job $job): void
    {
        $this->job = $job;
        $this->action = unserialize($job->payload);

        $this->printStart();
        $this->action->handle();
    }

    private function printStart(): void
    {
        $pid = getmypid();
        $id = $this->job->id;
        $class = get_class($this->action);
        $time = date('Y-m-d H:i:s');

        echo "$time [$pid] running  job: [$id] $class".PHP_EOL;
    }
}
