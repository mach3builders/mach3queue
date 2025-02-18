<?php

namespace Mach3queue\Supervisor;

use JsonException;

class SupervisorOptions
{
    public function __construct(
        public string $name = 'supervisor',
        public string $master = 'master',
        public array $queues = ['default'],
        public int $timeout = 60,
        public int $memory = 128,
        public int $maxProcesses = 1,
        public int $minProcesses = 1,
        public string $directory = '',
        public int $balanceCooldown = 5,
        public int $maxWorkload = 5,
        public int $maxRetries = 3,
        public int $timeToRetry = 300,
    ) {
    }

    public static function fromConfig(string $name, array $config): static
    {
        return new static(
            name: $name,
            queues: $config['queue'] ?? ['default'],
            timeout: $config['timeout'] ?? 60,
            memory: $config['memory'] ?? 128,
            maxProcesses: $config['max_processes'] ?? 1,
            minProcesses: $config['min_processes'] ?? 1,
            directory: $config['directory'] ?? self::binDir(),
            balanceCooldown: $config['balance_cooldown'] ?? 5,
            maxWorkload: $config['max_workload'] ?? 5,
            maxRetries: $config['max_retries'] ?? 3,
            timeToRetry: $config['time_to_retry'] ?? 60
        );
    }

    public static function binDir(): string
    {
        return dirname(__DIR__, 5) . '/vendor/bin';
    }

    public function toSupervisorCommand(): string
    {
        return SupervisorCommandString::fromOptions($this);
    }

    public function toWorkerCommand(): string
    {
        return WorkerCommandString::fromOptions($this);
    }

    public function toSuperVisorName(): string
    {
        return $this->name.':'.$this->master;
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode([
            'name' => $this->name,
            'queues' => implode(',', $this->queues),
            'timeout' => $this->timeout,
            'memory' => $this->memory,
            'maxProcesses' => $this->maxProcesses,
            'minProcesses' => $this->minProcesses,
            'directory' => $this->directory,
            'balanceCooldown' => $this->balanceCooldown,
            'maxWorkload' => $this->maxWorkload,
            'maxRetries' => $this->maxRetries,
            'timeToRetry' => $this->timeToRetry
        ], JSON_THROW_ON_ERROR);
    }
}