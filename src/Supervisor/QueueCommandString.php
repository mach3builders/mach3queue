<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Supervisor\SupervisorOptions;

class QueueCommandString
{
    public static function toWorkerOptionsString(SupervisorOptions $options): string
    {
        return sprintf(
            '--queue=%s --timeout=%s',
            implode(',', $options->queues),
            $options->timeout,
        );
    }

    public static function toSupervisorOptionsString(SupervisorOptions $options): string
    {
        return sprintf(
            '--max-processes=%s --queue=%s --master=%s --directory=%s --timeout=%s',
            $options->maxProcesses,
            implode(',', $options->queues),
            $options->master,
            $options->directory,
            $options->timeout,
        );
    }
}