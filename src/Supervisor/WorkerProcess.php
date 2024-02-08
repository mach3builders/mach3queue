<?php

namespace Mach3queue\Supervisor;

use Symfony\Component\Process\Process;

class WorkerProcess
{
    private Process $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }
}