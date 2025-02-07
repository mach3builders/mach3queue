<?php

namespace Mach3queue\Action;

use Mach3queue\Job\Job;
use Mach3queue\Stopwatch;
use function Opis\Closure\{unserialize};

class RunJob
{
    private Job $job;
    private mixed $payload;

    public function __invoke(Job $job): void
    {
        Stopwatch::start($job->id);
        $this->job = $job;
        $this->payload = unserialize($job->payload);

        $this->echoJobIsRunning();
        $this->payload->handle();

        if ($this->payload->after instanceof \Closure) {
            ($this->payload->after)($this->job);
        }
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
