<?php

namespace Mach3queue\Action;

use Illuminate\Support\Carbon;
use Mach3queue\Job\Job;

class BuryJob
{
    private Job $job;
    private mixed $action;
    private string $message;

    public function execute(Job $job, string $message): void
    {
        $this->job = $job;
        $this->action = unserialize($job->payload);
        $this->message = $message;

        $this->bury();
        $this->printBury();
    }

    private function bury(): void
    {
        $this->job->is_buried = 1;
        $this->job->buried_dt = Carbon::now();
        $this->job->is_reserved = 0;
        $this->job->reserved_dt = null;
        $this->job->message = $this->message;
        $this->job->save();
    }

    private function printBury(): void
    {
        $pid = getmypid();
        $id = $this->job->id;
        $class = get_class($this->action);

        echo "[$pid] buried   job: [$id] $class".PHP_EOL;
        echo "[$pid] message: $this->message".PHP_EOL;
    }
}