<?php

namespace Mach3queue\Worker;

use Mach3queue\Job\Job;
use Mach3queue\Action\BuryJob;
use Mach3queue\Action\CompleteJob;
use Mach3queue\Action\KillWorker;
use Mach3queue\Action\RunJob;
use Mach3queue\Action\TimeoutJob;
use Mach3queue\Action\JobMemoryExceeded;

/**
 * @method timeoutJob(Job $job)
 * @method killWorker()
 * @method runJob(Job $job)
 * @method completeJob(Job $job)
 * @method buryJob(Job $job, string $message)
 * @method jobMemoryExceeded(Job|null $job)
 * @method startTimingJob(Job $job)
 */
class WorkerActions
{
    public function __construct(
        public BuryJob $buryJob = new BuryJob,
        public CompleteJob $completeJob = new CompleteJob,
        public JobMemoryExceeded $jobMemoryExceeded = new JobMemoryExceeded,
        public KillWorker $killWorker = new KillWorker,
        public RunJob $runJob = new RunJob,
        public TimeoutJob $timeoutJob = new TimeoutJob,
    ) {
    }

    public function __call(string $method, array $parameters)
    {
        return ($this->$method)(...$parameters);
    }
}