<?php

namespace Mach3queue\Worker;

use Mach3queue\Action\BuryJob;
use Mach3queue\Action\CompleteJob;
use Mach3queue\Action\KillWorker;
use Mach3queue\Action\RunJob;
use Mach3queue\Action\TimeoutJob;

/**
 * @method timeoutJob(\Mach3queue\Job\Job $job)
 * @method killWorker()
 * @method runJob(\Mach3queue\Job\Job $job)
 * @method completeJob(\Mach3queue\Job\Job $job)
 * @method buryJob(\Mach3queue\Job\Job $job, string $message)
 */
class WorkerActions
{
    public function __construct(
        public RunJob $runJob = new RunJob,
        public CompleteJob $completeJob = new CompleteJob,
        public BuryJob $buryJob = new BuryJob,
        public TimeoutJob $timeoutJob = new TimeoutJob,
        public KillWorker $killWorker = new KillWorker,
    ) {
    }

    public function __call(string $method, array $parameters)
    {
        return ($this->$method)(...$parameters);
    }
}