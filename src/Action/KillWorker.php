<?php

namespace Mach3queue\Action;

use Mach3queue\Worker\Worker;

class KillWorker
{
    public function __invoke(): void
    {
        posix_kill(getmypid(), SIGKILL);
        exit(Worker::$EXIT_ERROR);
    }
}