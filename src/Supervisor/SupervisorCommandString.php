<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Supervisor\QueueCommandString;
use Mach3queue\Supervisor\SupervisorOptions;

class SupervisorCommandString
{
    public static $command = 'exec @php mach3 queue:supervisor';

    public static function fromOptions(SupervisorOptions $options)
    {
        $command = str_replace('@php', PHP_BINARY, static::$command);

        return sprintf(
            "%s %s",
            $command,
            QueueCommandString::toSupervisorOptionsString($options)
        );
    }
}