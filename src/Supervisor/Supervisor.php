<?php

namespace Mach3queue\SuperVisor;

use Closure;
use Illuminate\Support\Collection;
use Mach3queue\Supervisor\ProcessPool;
use Mach3queue\Supervisor\SupervisorOptions;

class Supervisor
{
    private SupervisorOptions $options;

    private ProcessPool $process_pool;

    private Closure $output;

    public function __construct(SupervisorOptions $options)
    {
        $this->options = $options;
        $this->process_pool = $this->createProcessPool();
        $this->output = fn() => null;
    }

    public function scale(int $processes): void
    {
        $processes = max(0, $processes);
        $processes = min($processes, $this->options->maxProcesses);
        
        $this->process_pool->scale($processes);
    }

    public function handleOutputUsing(Closure $callback): void
    {
        $this->output = $callback;
    }

    public function output($type, $line)
    {
        call_user_func($this->output, $type, $line);
    }

    public function monitor(): void
    {
        while (true) {
            sleep(1);
            $this->loop();
        }
    }

    public function loop(): void
    {
        $this->process_pool->monitor();
    }

    public function processes(): Collection
    {
        return $this->process_pool->processes();
    }

    public function terminatingProcesses(): Collection
    {
        return $this->process_pool->terminatingProcesses();
    }
    
    private function createProcessPool(): ProcessPool
    {
        return new ProcessPool($this->options, function ($type, $line) {
            $this->output($type, $line);
        });
    }
}