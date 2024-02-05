<?php

namespace Mach3queue\Action;

use Mach3queue\Worker\Worker;

class KillWorker implements Action
{
    public function execute(...$args): void
    {
        posix_kill(getmypid(), SIGKILL);
        exit(Worker::EXIT_ERROR);
    }
}