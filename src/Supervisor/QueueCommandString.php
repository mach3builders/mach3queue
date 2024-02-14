<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Supervisor\SupervisorOptions;

class QueueCommandString
{
    public static function toWorkerOptionsString(SupervisorOptions $options): string
    {
        return sprintf(
            '--max-processes=%d',
            $options->maxProcesses,
        );
    }
}