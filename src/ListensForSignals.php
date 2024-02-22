<?php

namespace Mach3queue;

use Illuminate\Support\Arr;

trait ListensForSignals
{
    protected array $pendingSignals = [];

    protected function listenForSignals(): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGINT, function () {
            $this->pendingSignals['terminate'] = 'terminate';
        });

        pcntl_signal(SIGTERM, function () {
            $this->pendingSignals['terminate'] = 'terminate';
        });

        pcntl_signal(SIGUSR1, function () {
            $this->pendingSignals['restart'] = 'restart';
        });

        pcntl_signal(SIGUSR2, function () {
            $this->pendingSignals['pause'] = 'pause';
        });

        pcntl_signal(SIGCONT, function () {
            $this->pendingSignals['continue'] = 'continue';
        });
    }

    /**
     * Process the pending signals.
     *
     * @return void
     */
    protected function processPendingSignals(): void
    {
        while ($this->pendingSignals) {
            $signal = Arr::first($this->pendingSignals);

            $this->{$signal}();

            unset($this->pendingSignals[$signal]);
        }
    }
}