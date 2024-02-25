<?php

namespace Mach3queue\Supervisor;

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
        );
    }

    public static function binDir(): string
    {
        return realpath(__DIR__.'/../../vendor/bin');
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
        return 'supervisor:'.$this->name.':'.$this->master;
    }

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
        ]);
    }
}