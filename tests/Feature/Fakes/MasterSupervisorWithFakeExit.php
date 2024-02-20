<?php

namespace Tests\Feature\Fakes;

use Mach3queue\Supervisor\MasterSupervisor;

class MasterSupervisorWithFakeExit extends MasterSupervisor
{
    public $exited = false;

    protected function exit(int $status = 0): void
    {
        $this->exited = true;
    }
}