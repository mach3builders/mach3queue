<?php

namespace Tests\Feature\Fakes;

use Mach3queue\Supervisor\Supervisor;

class SupervisorWithFakeExit extends Supervisor
{
    public $exited = false;

    protected function exit(int $status = 0): void
    {
        $this->exited = true;
    }
}