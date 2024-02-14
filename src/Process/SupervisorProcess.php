<?php

namespace Mach3queue\Process;

use Mach3queue\Supervisor\SupervisorOptions;
use Symfony\Component\Process\Process;

class SupervisorProcess extends QueueProcess
{
    public bool $dead = false;

    private SupervisorOptions $options;

    public function __construct(SupervisorOptions $options, Process $process)
    {
        $this->options = $options;

        parent::__construct($process);
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