<?php

namespace Mach3queue\Supervisor;

class SupervisorOptions
{
    public function __construct(
        public string $queues = 'default',
        public int $maxProcesses = 1,
        public string $directory = __DIR__,
    ) {
    }

    public function toWorkerCommand(): string
    {
        return WorkerCommandString::fromOptions($this);
    }
}