<?php

namespace Mach3queue\Supervisor;

class QueueCommandString
{
    public static function toWorkerOptionsString(SupervisorOptions $options): string
    {
        return sprintf(
            '-- --queue=%s --timeout=%s --memory=%s --max-retries=%s --time-to-retry=%s',
            implode(',', $options->queues),
            $options->timeout,
            $options->memory,
            $options->maxRetries,
            $options->timeToRetry
        );
    }

    public static function toSupervisorOptionsString(SupervisorOptions $options): string
    {
        return sprintf(
            '-- --name=%s --max-processes=%s --min-processes=%s --queue=%s --master=%s --directory=%s --timeout=%s --max-retries=%s --time-to-retry=%s',
            $options->name,
            $options->maxProcesses,
            $options->minProcesses,
            implode(',', $options->queues),
            $options->master,
            $options->directory,
            $options->timeout,
            $options->maxRetries,
            $options->timeToRetry,
        );
    }
}