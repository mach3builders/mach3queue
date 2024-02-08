<?php

namespace Mach3queue\SuperVisor;

use Closure;
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

    private function createProcessPool(): ProcessPool
    {
        return new ProcessPool($this->options, function ($type, $line) {
            $this->output($type, $line);
        });
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

            $this->process_pool->monitor();
        }
    }
}