<?php

namespace Mach3queue\Queue;

/**
 * @method static addJob(Queueable $job, int $delay=0, int $priority=1024, int $time_to_retry=60)
 * @method static getNextJob()
 * @method static deleteJob(int $id)
 * @method static on(string $string)
 * @method static pipelines(string[] $array)
 * @method setConnection(string[] $array)
 */
class QueueManager
{
    protected static QueueManager $instance;

    protected Queue $queue;

    public function __construct()
    {
        $this->queue = new Queue;
    }

    public function setAsGlobal(): void
    {
        static::$instance = $this;
    }

    public function getInstance(): Queue
    {
        return $this->queue;
    }

    public function __call(string $method, array $parameters)
    {
        return $this->queue->$method(...$parameters);
    }

    public static function __callStatic(string $method, array $parameters)
    {
        return static::$instance->$method(...$parameters);
    }
}