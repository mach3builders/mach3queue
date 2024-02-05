<?php

namespace Mach3queue\Queue;

class FakeEmptyQueueable implements Queueable
{
    public function handle(): void
    {
    }
}