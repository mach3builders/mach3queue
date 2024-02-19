<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Supervisor\SupervisorOptions;

class QueueCommandString
{
    public static function toWorkerOptionsString(SupervisorOptions $options): string
    {
        return sprintf(
            '--max-processes=%s --queue=%s',
            $options->maxProcesses,
            implode(',', $options->queues),
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