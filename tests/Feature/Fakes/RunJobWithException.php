<?php

namespace Tests\Feature\Fakes;

use Exception;
use Mach3queue\Job\Job;
use Mach3queue\Action\RunJob;

class RunJobWithException extends RunJob
{
    /**
     * @throws Exception
     */
    public function __invoke(Job $job): void
    {
        throw new Exception('This is a test exception');
    }
}