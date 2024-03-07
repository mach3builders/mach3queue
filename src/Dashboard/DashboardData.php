<?php

namespace Mach3queue\Dashboard;

use Mach3queue\Job\Job;
use Illuminate\Support\Collection;
use Mach3queue\Supervisor\SupervisorRepository;

class DashboardData
{
    private Collection $supervisors;
    private Collection $queue_workload;
    private bool|int $master_index;
    private bool $active;

    public function __construct()
    {
        $this->supervisors = collect(SupervisorRepository::all());
        $this->master_index = $this->getMasterIndex();
        $this->active = $this->master_index !== false;
        $this->queue_workload = Job::queuesWorkload()->get()->keyBy('queue');

        if ($this->master_index !== false) {
            $this->supervisors->forget($this->master_index);
        }
    }

    public function get(): string
    {
        return json_encode([
            'active' => $this->active,
            'supervisors' => $this->getSupervisors(),
            'completedJobs' => $this->getCompletedJobsAmount(),
            'failedJobs' => $this->getFailedJobsAmount(),
            'pendingJobs' => $this->getPendingJobsAmount(),
            'queues' => $this->getQueuesWorkload(),
        ]);

    }

    private function getMasterIndex(): bool|int
    {
        return $this->supervisors->search(fn($s) => $s->master == null);
    }

    private function getSupervisors(): Collection
    {
        if (! $this->active) {
            return collect();
        }

        return $this->supervisors->values();
    }

    private function getCompletedJobsAmount(): int
    {
        return Job::completed()->count();
    }

    private function getFailedJobsAmount(): int
    {
        return Job::failed()->count();
    }

    private function getPendingJobsAmount()
    {
        return Job::pending()->count();
    }

    private function getQueuesWorkload(): array
    {
        $queues_workload = [];

        if (! $this->active) {
            return [];
        }

        foreach ($this->supervisors as $supervisor) {
            $supervisor->options = json_decode($supervisor->options);
            $queues = explode(',', $supervisor->options->queues);

            foreach ($queues as $queue) {
                if (! array_key_exists($queue, $queues)) {
                    $queues_workload[$queue] = ['name' => $queue, 'count' => 0];
                }

                $count = $this->queue_workload[$queue]?->count ?? 0;
                $queues_workload[$queue]['count'] += $count;
            }
        }

        return array_values($queues_workload);
    }
}