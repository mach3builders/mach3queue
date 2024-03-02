<?php

namespace Mach3queue\Action;

use Mach3queue\Queue\QueueManager as Queue;
use Symfony\Component\Console\Input\InputInterface;

class CreateQueueFromConsole
{
    public static function create(InputInterface $input, array $database): void
    {
        $queue = new Queue;
        $queue->pipelines(GetQueueNamesFromConsole::get($input));
        $queue->setConnection([
            'driver' => $database['driver'] ?? 'mysql',
            'host' => $database['host'],
            'database' => $database['database'],
            'username' => $database['username'],
            'password' => $database['password'],
        ]);
        $queue->setAsGlobal();
    }
}