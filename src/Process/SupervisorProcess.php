<?php

namespace Mach3queue\Process;

use Closure;
use Mach3queue\Supervisor\SupervisorOptions;
use Symfony\Component\Process\Process;

class SupervisorProcess extends QueueProcess
{
    public string $name;

    public bool $dead = false;

    public SupervisorOptions $options;

    public function __construct(
        SupervisorOptions $options,
        Process $process,
        Closure $output = null
    ) {
        $this->options = $options;
        $this->name = $options->name;

        parent::__construct($process, $output);
    }

    public function monitor(): void
    {
        if (! $this->process->isStarted()) {
            $this->restart();

            return;
        }

        if ($this->process->isRunning()) {
            // we are fine so don't do anything
            return;
        }

        // we are not fine, so mark as dead
        $this->dead = true;
    }
}