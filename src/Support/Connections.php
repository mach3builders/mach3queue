<?php

namespace Mach3queue\Support;

use Closure;
use Illuminate\Database\DetectsLostConnections;

/**
 * Long-running workers outlive their database connections. The queue's own
 * connection is refreshed by Illuminate, but the application connections a
 * job (or its after-callback) uses are owned by the application, so it has
 * to hand us the way to refresh them.
 */
class Connections
{
    use DetectsLostConnections;

    private static ?Closure $refresh = null;

    public static function refreshUsing(?callable $callback): void
    {
        self::$refresh = $callback ? Closure::fromCallable($callback) : null;
    }

    public static function refresh(): void
    {
        if (self::$refresh) {
            (self::$refresh)();
        }
    }

    public static function forgetRefresh(): void
    {
        self::$refresh = null;
    }

    /**
     * Run the callback, refreshing the connections and retrying once when it
     * fails on a connection that went away while the worker was busy.
     */
    public static function retrying(callable $callback): mixed
    {
        return (new static)->attempt($callback);
    }

    private function attempt(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            if (! $this->causedByLostConnection($e)) {
                throw $e;
            }

            static::refresh();

            return $callback();
        }
    }
}
