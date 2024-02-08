<?php

namespace Mach3queue\Worker;

use Mach3queue\Action\BuryJob;
use Mach3queue\Action\CompleteJob;
use Mach3queue\Action\KillWorker;
use Mach3queue\Action\RunJob;
use Mach3queue\Action\TimeoutJob;

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
}