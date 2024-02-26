<?php

namespace Mach3queue\Supervisor;

use Mach3queue\Queue\QueueManager as Queue;

class AutoScaler
{
    private int $max;
    private int $min;
    private int $max_workload;
    private int $current;
    private int|float $workload;

    public function scale(Supervisor $supervisor): void
    {
        $this->max = $supervisor->options->maxProcesses;
        $this->min = $supervisor->options->minProcesses;
        $this->max_workload = $supervisor->options->maxWorkload;
        $this->current = $supervisor->process_pool->runningProcesses()->count();
        $this->workload = $this->workload();


        if ($this->ifWorkloadIsHighAndWeAreNotAtMax()) {
            $supervisor->scale($this->current + 1);
        } elseif ($this->ifWorkloadIsLowAndWeAreNotAtMin()) {
            $supervisor->scale($this->current - 1);
        }
    }

    private function workload(): int|float
    {
        $total_jobs = Queue::getTotalJobsInQueue();

        if ($this->current == 0 || $total_jobs == 0) {
            return $total_jobs;
        }

        return $total_jobs / $this->current;
    }

    private function ifWorkloadIsHighAndWeAreNotAtMax(): bool
    {
        return $this->workload > $this->max_workload
            && $this->current < $this->max;
    }

    private function ifWorkloadIsLowAndWeAreNotAtMin(): bool
    {
        return $this->workload < $this->max_workload
            && $this->current > $this->min;
    }
}