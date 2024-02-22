<?php

namespace Mach3queue\Supervisor;

use Mach3queue\SuperVisor\Supervisor;

class AutoScalar
{
    public function scale(Supervisor $supervisor): void
    {
        $max = $supervisor->options->maxProcesses;
        $min = $supervisor->options->minProcesses;
    }
}