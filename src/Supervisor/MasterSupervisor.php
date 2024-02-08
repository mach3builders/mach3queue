<?php

namespace Mach3queue\Supervisor;

use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

class MasterSupervisor
{
    public Collection $supervisors;

    public function __construct()
    {
        $this->supervisors = new Collection;
    }

    public function addSupervisorProcess(
        SupervisorOptions $options,
        Process $process
    ): void {
        $this->supervisors->push(new SupervisorProcess($options, $process));
    }

    public function loop(): void
    {
        $this->supervisors->each->monitor();
        $this->rejectSupervisorsThatAreDead();
    }

    private function rejectSupervisorsThatAreDead(): void
    {
        $this->supervisors = $this->supervisors->reject->dead;
    }
}