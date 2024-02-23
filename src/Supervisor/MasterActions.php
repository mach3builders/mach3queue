<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Action\ExpireSupervisors;

/**
 * @method expireSupervisors()
 */
class MasterActions
{
    public function __construct(
        public ExpireSupervisors $expireSupervisors = new ExpireSupervisors
    ) {
    }

    public function __call(string $method, array $parameters)
    {
        return ($this->$method)(...$parameters);
    }
}