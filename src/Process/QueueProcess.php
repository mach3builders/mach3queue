<?php

namespace Mach3queue\Process;

use Closure;
use Exception;
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

    protected function restart(): void
    {
        $this->start($this->output);
    }

    public function start(Closure $callback): void
    {
        $this->output = $callback;

        $this->process->start($callback);
    }

    /**
     * @throws Exception
     */
    public function pause(): void
    {
        $this->sendSignal(SIGUSR2);
    }

    /**
     * @throws Exception
     */
    public function continue(): void
    {
        $this->sendSignal(SIGCONT);
    }

    /**
     * @throws Exception
     */
    public function terminate(): void
    {
        $this->sendSignal(SIGTERM);
    }

    public function stop(): void
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

    public function isIdleFor(int $seconds): bool
    {
        return $this->getIdleTime() >= $seconds;
    }

    /**
     * @throws Exception
     */
    private function sendSignal(int $signal): void
    {
        try {
            $this->process->signal($signal);
        } catch (Exception $e) {
            if ($this->process->isRunning()) {
                throw $e;
            }
        }
    }

    public function handleOutputUsing(Closure $callback): static
    {
        $this->output = $callback;

        return $this;
    }

    public function __call($method, $parameters)
    {
        return $this->process->{$method}(...$parameters);
    }
}