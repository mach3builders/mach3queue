<?php

namespace Mach3queue\Queue;

use Mach3queue\Queue\Queue;

class QueueManager
{
    /**
     * The current globally used instance.
     *
     * @var object
     */
    protected static $instance;

    protected Queue $queue;

    public function __construct()
    {
        $this->queue = new Queue;
    }

    /**
     * Make this capsule instance available globally.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    public function getInstance(): Queue
    {
        return $this->queue;
    }

    /**
     * Pass dynamic instance methods to the manager.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->queue->$method(...$parameters);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::$instance->$method(...$parameters);
    }
}