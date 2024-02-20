<?php

namespace Mach3queue\Supervisor;

use Carbon\CarbonImmutable;
use Closure;
use Countable;
use Illuminate\Support\Collection;
use Mach3queue\Process\WorkerProcess;
use Symfony\Component\Process\Process;

class ProcessPool implements Countable
{
    private Collection $processes;

    private Collection $terminatingProcesses;

    private SupervisorOptions $options;

    private Closure $output;

    public function __construct(SupervisorOptions $options, ?\Closure $output = null)
    {
        $this->processes = new Collection;
        $this->terminatingProcesses = new Collection;
        $this->options = $options;
        $this->output = $output ?: fn() => null;
    }

    public function scale(int $process_amount): void
    {
        $process_amount = max(0, $process_amount);
        
        if ($process_amount === $this->processes->count()) {
            return;
        }
        
        if ($process_amount > $this->processes->count()) {
            $this->scaleUp($process_amount);
        } else {
            $this->scaleDown($process_amount);
        }
    }

    private function scaleUp(int $process_amount)
    {
        $difference = $process_amount - $this->processes->count();

        for ($i = 0; $i < $difference; $i++) {
            $this->start();
        }
    }

    private function scaleDown(int $process_amount)
    {
        $difference = $process_amount - $this->processes->count();
        $terminatingProcesses = $this->processes->slice(0, $difference);

        foreach ($terminatingProcesses as $process) {
            $this->markForTermination($process);
            $process->terminate();
        }

        $this->removeProcesses($difference);
    }

    private function markForTermination(WorkerProcess $process): void
    {
        $this->terminatingProcesses->push([
            'process' => $process,
            'terminatedAt' => CarbonImmutable::now()
        ]);
    }

    private function removeProcesses(int $amount): void
    {
        $this->processes = $this->processes->slice($amount);
    }

    private function start(): void
    {
        $worker_process = $this->createProcess();
        $worker_process->handleOutputUsing(function ($type, $line) {
            call_user_func($this->output, $type, $line);
        });

        $this->processes->push($worker_process);
    }

    private function createProcess(): WorkerProcess
    {
        $command = $this->options->toWorkerCommand();
        $directory = $this->options->directory;
        $process = Process::fromShellCommandline($command, $directory)
            ->setTimeout(null)
            ->disableOutput();

        return new WorkerProcess($process);
    }

    public function monitor(): void
    {
        $this->processes->each->monitor();
    }

    public function terminate(): void
    {
        $this->processes->each->terminate();
    }

    public function count(): int
    {
        return $this->processes->count();
    }

    public function processes(): Collection
    {
        return $this->processes;
    }

    public function terminatingProcesses(): Collection
    {
        return $this->terminatingProcesses;
    }

    public function runningProcesses(): Collection
    {
        return $this->processes->filter->isRunning();
    }
}