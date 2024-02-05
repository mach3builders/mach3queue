<?php

namespace Mach3queue\Worker;

use Mach3queue\Action\Action;
use Mach3queue\Action\KillWorker;
use Mach3queue\Action\TimeoutJob;
use Mach3queue\Job\Job;

class WorkerActions
{
    public function __construct(
        private Action $timeout_job = new TimeoutJob,
        private Action $kill_worker = new KillWorker,
    ) {
    }

    public function timeoutJob(Job $job): void
    {
        $this->timeout_job->execute(job:$job);
    }

    public function killWorker(): void
    {
        $this->kill_worker->execute();
    }
}