<?php

namespace Mach3queue\Supervisor;

use Symfony\Component\Process\Process;

class SupervisorProcess
{
    public bool $dead = false;

    private SupervisorOptions $options;

    private Process $process;

    public function __construct( SupervisorOptions $options, Process $process)
    {
        $this->options = $options;
        $this->process = $process;
    }

    public function monitor(): void
    {
        if ($this->process->isRunning()) {
            // we are fine so don't do anything
            return;
        }

        // we are not fine, so mark as dead
        $this->dead = true;
    }
}