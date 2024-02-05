<?php

namespace Mach3queue\Worker;

class WorkerOptions
{
    public function __construct(
        public bool $stop_when_empty = false,
    ) {
    }
}