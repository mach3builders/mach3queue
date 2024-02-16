<?php

namespace Mach3queue\Process;

class WorkerProcess extends QueueProcess
{
    public function monitor(): void
    {
        if ($this->process->isRunning()) {
            return;
        }

        $this->restart();
    }
}