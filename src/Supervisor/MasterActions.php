<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Action\TrimOldJobs;
use Mach3queue\Action\ExpireSupervisors;

/**
 * @method expireSupervisors()
 * @method trimOldJobs()
 */
class MasterActions
{
    public function __construct(
        public ExpireSupervisors $expireSupervisors = new ExpireSupervisors,
        public TrimOldJobs $trimOldJobs = new TrimOldJobs
    ) {
    }

    public function __call(string $method, array $parameters)
    {
        return ($this->$method)(...$parameters);
    }
}