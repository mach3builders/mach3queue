<?php

namespace Mach3queue\Supervisor;

class SupervisorOptions
{
    public function __construct(
        public string $name = 'Supervisor',
        public string $master = 'master',
        public array $queues = ['default'],
        public int $maxProcesses = 1,
        public string $directory = __DIR__,
    ) {
    }

    public static function fromConfig(string $name, array $config): static
    {
        return new static(
            name: $name,
            queues: $config['queues'] ?? ['default'],
            maxProcesses: $config['max_processes'] ?? 1,
            directory: $config['directory'] ?? __DIR__,
        );
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
            'maxProcesses' => $this->maxProcesses,
            'directory' => $this->directory,
        ]);
    }
}