<?php

namespace Mach3queue\Process;

use Closure;
use Symfony\Component\Process\Process;

class QueueProcess
{
    public Process $process;

    public Closure $output;

    public function __construct(Process $process, Closure $output = null)
    {
        $this->process = $process;
        $this->output = $output ?: fn() => null;
    }

    protected function restart()
    {
        $this->start($this->output);
    }

    public function start(Closure $callback): void
    {
        $this->output = $callback;
        
        $this->process->start($callback);
    }

    public function pause()
    {
        $this->sendSignal(SIGUSR2);
    }

    public function continue()
    {
        $this->sendSignal(SIGCONT);
    }

    public function terminate()
    {
        $this->sendSignal(SIGTERM);
    }

    public function stop()
    {
        if (!$this->process->isRunning()) {
            return;
        }
        
        $this->process->stop();
    }

    public function getIdleTime(): float
    {
        return microtime(true) - $this->process->getLastOutputTime();
    }

    private function sendSignal(int $signal)
    {
        try {
            $this->process->signal($signal);
        } catch (\Exception $e) {
            if ($this->process->isRunning()) {
                throw $e;
            }
        }
    }

    public function handleOutputUsing(Closure $callback)
    {
        $this->output = $callback;

        return $this;
    }

    public function __call($method, $parameters)
    {
        return $this->process->{$method}(...$parameters);
    }
}