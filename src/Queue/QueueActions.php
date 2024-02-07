<?php

namespace Mach3queue\Queue;

use Mach3queue\Action\AddJob;
use Mach3queue\Action\ReserveJob;
use Mach3queue\Job\Job;

class QueueActions
{

    public function __construct(
        private $reserve_job = new ReserveJob,
        private $add_job = new AddJob,
    ) {
    }

    public function reserveJob(Job $job): void
    {
        $this->reserve_job->execute(job: $job);
    }

    public function addJob(
        string $queue,
        string $payload,
        int $delay,
        int $priority,
        int $time_to_retry
    ): Job {
        return $this->add_job->execute(
            queue: $queue,
            payload: $payload,
            delay: $delay,
            priority: $priority,
            time_to_retry: $time_to_retry,
        );
    }
}