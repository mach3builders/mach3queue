<?php

namespace Mach3queue\Supervisor;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Collection;
use Mach3queue\ListensForSignals;

class Supervisor
{
    use ListensForSignals;

    public string $name;
    public Closure $output;
    public SupervisorOptions $options;
    public bool $working = true;
    public ProcessPool $process_pool;
    public CarbonImmutable $last_auto_scaled;
    public AutoScaler $auto_scalar;

    public function __construct(
        SupervisorOptions $options,
        AutoScaler $auto_scalar = new AutoScaler
    ) {
        $this->options = $options;
        $this->auto_scalar = $auto_scalar;
        $this->name = $options->toSupervisorName();
        $this->process_pool = $this->createProcessPool();
        $this->output = fn() => null;
    }

    public function scale(int $processes): void
    {
        $min = $this->options->minProcesses;
        $max = $this->options->maxProcesses;

        $this->process_pool->scale(max($min, min($max, $processes)));
    }

    public function handleOutputUsing(Closure $callback): void
    {
        $this->output = $callback;
    }

    public function output($type, $line): void
    {
        call_user_func($this->output, $type, $line);
    }

    public function monitor(): void
    {
        $this->listenForSignals();
        $this->persist();

        while (true) {
            sleep(1);
            $this->loop();
        }
    }

    public function loop(): void
    {
        $this->processPendingSignals();

        if ($this->working) {
            $this->autoScale();
            $this->process_pool->monitor();
        }

        $this->persist();
    }

    public function terminate(int $status = 0): void
    {
        $this->working = false;

        SupervisorRepository::forget($this->name);

        $this->process_pool->terminate();

        while ($this->process_pool->runningProcesses()->count()) {
            sleep(1);
        }

        $this->exit($status);
    }

    public function processes(): Collection
    {
        return $this->process_pool->processes();
    }

    public function terminatingProcesses(): Collection
    {
        return $this->process_pool->terminatingProcesses();
    }

    public function pid(): bool|int
    {
        return getmypid();
    }

    private function persist(): void
    {
        SupervisorRepository::updateOrCreate($this);
    }

    private function autoScale(): void
    {
        $this->initLastAutoScaled();

        if ($this->timePassedForAutoScale()) {
            $this->last_auto_scaled = CarbonImmutable::now();
            $this->auto_scalar->scale($this);
        }
    }

    private function initLastAutoScaled(): void
    {
        if (isset($this->last_auto_scaled)) {
            return;
        }

        $this->last_auto_scaled = CarbonImmutable::now()
            ->subSeconds($this->options->balanceCooldown + 1);
    }

    private function timePassedForAutoScale(): bool
    {
        return CarbonImmutable::now()
            ->subSeconds($this->options->balanceCooldown)
            ->gte($this->last_auto_scaled);
    }

    private function createProcessPool(): ProcessPool
    {
        return new ProcessPool($this->options, function ($type, $line) {
            $this->output($type, $line);
        });
    }

    protected function exit(int $status = 0): void
    {
        exit($status);
    }
}