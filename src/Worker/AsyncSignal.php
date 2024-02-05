<?php

namespace Mach3queue\Worker;

use Mach3queue\Worker\Worker;

class AsyncSignal
{
    const EXTENSION_MISSING = 'The pcntl extension is required for Mach3queue to function';

    private int $timeout;

    public function __construct()
    {
        if (!extension_loaded('pcntl')) {
            throw new \RuntimeException(self::EXTENSION_MISSING);
        }

        pcntl_async_signals(true);
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function listenForSignalsOnWorker(Worker $worker): void
    {
        pcntl_signal(SIGTERM, fn() => $worker->quit());
        pcntl_signal(SIGUSR2, fn() => $worker->pause());
        pcntl_signal(SIGCONT, fn() => $worker->resume());
    }

    public function registerTimeoutHandlerForJob(callable $callback): void
    {
        pcntl_signal(SIGALRM, $callback);
        pcntl_alarm(max($this->timeout, 0));
    }

    public function resetTimeoutHandler(): void
    {
        pcntl_alarm(0);
    }
}