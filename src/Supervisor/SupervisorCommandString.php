<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Supervisor\QueueCommandString;
use Mach3queue\Supervisor\SupervisorOptions;

class SupervisorCommandString
{
    public static string $command = 'exec @php queue supervisor';

    public static function fromOptions(SupervisorOptions $options): string
    {
        $command = str_replace('@php', PHP_BINARY, static::$command);

        return sprintf(
            "%s %s",
            $command,
            QueueCommandString::toSupervisorOptionsString($options)
        );
    }
}