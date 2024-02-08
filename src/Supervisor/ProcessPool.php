<?php

namespace Mach3queue\Supervisor;

use Closure;
use Countable;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

class ProcessPool implements Countable
{
    private Collection $processes;

    private SupervisorOptions $options;

    private Closure $output;

    public function __construct(SupervisorOptions $options, ?\Closure $output = null)
    {
        $this->processes = new Collection;
        $this->options = $options;
        $this->output = $output ?: fn() => null;
    }

    public function monitor(): void
    {
        foreach ($this->processes as $process) {
            $process->each->monitor();
        }
    }

    public function count(): int
    {
        return $this->processes->count();
    }

    private function startNewProcess(): void
    {
        $this->createWorkerProcess();
    }

    private function createWorkerProcess(): WorkerProcess
    {
        $process = Process::fromShellCommandline(
            $this->options->toWorkCommand(),
            $this->options->directory
        )->setTimeout(null)
        ->disableOutput();

        return new WorkerProcess($process);
    }
}