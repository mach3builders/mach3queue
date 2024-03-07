<?php

namespace Mach3queue;

class Stopwatch
{
    public static array $timers = [];

    public static function start(mixed $key): void
    {
        self::$timers[$key] = microtime(true);
    }

    public static function check($key): float
    {
        if (isset(self::$timers[$key])) {
            return round((microtime(true) - self::$timers[$key]) * 1000, 2);
        }

        return 0;
    }

    public static function forget($key): void
    {
        unset(self::$timers[$key]);
    }
}