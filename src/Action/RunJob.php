<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;

class RunJob
{
    private Job $job;
    private mixed $payload;

    public function __invoke(Job $job): void
    {
        $this->job = $job;
        $this->payload = unserialize($job->payload);

        $this->echoJobIsRunning();
        $this->payload->handle();
    }

    private function echoJobIsRunning(): void
    {
        $pid = getmypid();
        $id = $this->job->id;
        $class = get_class($this->payload);
        $time = date('Y-m-d H:i:s');

        echo "$time [$pid] running  job: [$id] $class".PHP_EOL;
    }
}
