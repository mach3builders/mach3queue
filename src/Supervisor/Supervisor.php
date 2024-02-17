<?php

namespace Mach3queue\SuperVisor;

use Closure;
use Illuminate\Support\Collection;
use Mach3queue\ListensForSignals;
use Mach3queue\Supervisor\ProcessPool;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\SupervisorRepository;

class Supervisor
{
    use ListensForSignals;

    public string $name;

    public bool $working = true;
    
    public SupervisorOptions $options;

    public ProcessPool $process_pool;

    public Closure $output;

    public function __construct(SupervisorOptions $options)
    {
        $this->options = $options;
        $this->name = $options->toSupervisorName();
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
        $this->listenForSignals();
        $this->update();

        while (true) {
            sleep(1);
            $this->loop();
        }
    }

    public function loop(): void
    {
        $this->process_pool->monitor();
        $this->processPendingSignals();
        $this->update();
    }

    public function processes(): Collection
    {
        return $this->process_pool->processes();
    }

    public function terminatingProcesses(): Collection
    {
        return $this->process_pool->terminatingProcesses();
    }

    public function pid()
    {
        return getmypid();
    }

    private function update(): void
    {
        SupervisorRepository::updateOrCreate($this);
    }
    
    private function createProcessPool(): ProcessPool
    {
        return new ProcessPool($this->options, function ($type, $line) {
            $this->output($type, $line);
        });
    }
}