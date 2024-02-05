<?php

namespace Mach3queue;

class Command
{
    private array $data = [];

    public function __construct($data)
    {
        parse_str(implode('&', array_slice($data, 1)), $this->data);
    }

    public function getQueue(): string
    {
        return $this->data['--queue'] ?? 'default';
    }

    public function getTimeout(): string
    {
        return $this->data['--timeout'] ?? 60;
    }

    public function getStopWhenEmpty(): bool
    {
        return isset($this->data['--stop-when-empty']);
    }

    public function printProcessingJob(array $job): void
    {
        echo "Processing job: {$job['id']}". PHP_EOL;
    }
}
