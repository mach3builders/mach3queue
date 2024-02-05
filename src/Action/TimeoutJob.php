<?php

namespace Mach3queue\Action;

class TimeoutJob implements Action
{
    public function execute(...$args): void
    {
        $args['job']->timedOut();
    }
}