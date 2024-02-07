<?php

namespace Mach3queue\Worker;

use Mach3queue\Action\BuryJob;
use Mach3queue\Action\KillWorker;
use Mach3queue\Action\RunJob;
use Mach3queue\Action\TimeoutJob;
use Mach3queue\Job\Job;

class WorkerActions
{

    public function __construct(
        private $run_job = new RunJob,
        private $bury_job = new BuryJob,
        private $timeout_job = new TimeoutJob,
        private $kill_worker = new KillWorker,
    ) {
    }

    public function runJob(Job $job): void
    {
        $this->run_job->execute(job: $job);
    }

    public function buryJob(Job $job, string $message): void
    {
        $this->bury_job->execute(job: $job, message: $message);
    }

    public function timeoutJob(Job $job): void
    {
        $this->timeout_job->execute(job: $job);
    }

    public function killWorker(): void
    {
        $this->kill_worker->execute();
    }
}