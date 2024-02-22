<?php

namespace Mach3queue\Queue;

class FakeSleepQueueable implements Queueable
{
    public function __construct(private readonly int $seconds)
    {
    }

    public function handle(): void
    {
        sleep($this->seconds);
    }
}