<?php

namespace Mach3queue\Queue;

use Mach3queue\Action\AddJob;
use Mach3queue\Action\ReserveJob;
use Mach3queue\Job\Job;

/**
 * @method addJob(string $queue, string $payload, int $delay, int $priority, int $time_to_retry): Job
 * @method reserveJob(Job $job)
 */
class QueueActions
{
    public function __construct(
        private $reserveJob = new ReserveJob,
        private $addJob = new AddJob,
    ) {
    }

    public function __call(string $method, array $parameters)
    {
        return ($this->$method)(...$parameters);
    }
}