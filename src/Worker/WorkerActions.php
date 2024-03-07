<?php

namespace Mach3queue\Worker;

use Mach3queue\Action\BuryJob;
use Mach3queue\Action\CompleteJob;
use Mach3queue\Action\KillWorker;
use Mach3queue\Action\RunJob;
use Mach3queue\Action\TimeoutJob;
use Mach3queue\Action\StartTimingJob;
use Mach3queue\Action\JobMemoryExceeded;

/**
 * @method timeoutJob(\Mach3queue\Job\Job $job)
 * @method killWorker()
 * @method runJob(\Mach3queue\Job\Job $job)
 * @method completeJob(\Mach3queue\Job\Job $job)
 * @method buryJob(\Mach3queue\Job\Job $job, string $message)
 * @method jobMemoryExceeded(\Mach3queue\Job\Job|null $job)
 * @method startTimingJob(\Mach3queue\Job\Job $job)
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