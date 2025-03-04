<?php

namespace Mach3queue\Worker;

class WorkerOptions
{
    public function __construct(
        public bool $stop_when_empty = false,
        public int $memory = 128,
        public int $time_to_retry = 60,
        public int $max_retries = 3,
    ) {
    }
}