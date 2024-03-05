<?php

namespace Mach3queue\Queue;

class FakeEmptyQueueable implements Queueable
{
    public int $test = 10;

    public function handle(): void
    {
    }
}