<?php

namespace Mach3queue\Action;

use Mach3queue\Queue\Queue;
use Symfony\Component\Console\Input\InputInterface;

class GetQueueNamesFromConsole
{
    public static function get(InputInterface $input): array
    {
        $queue = $input->getOption('queue');

        return $queue ? explode(',', $queue) : [Queue::$default_queue];
    }
}