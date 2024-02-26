<?php

use Mach3queue\Job\Job;
use Carbon\CarbonImmutable;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\WorkerCommandString;

function advanceTimeByMinutes(int $minutes): void
{
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinutes($minutes));
}

function advanceTimeBySeconds(int $seconds): void
{
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addSeconds($seconds));
}

function createCompletedJobAtTime(CarbonImmutable $time): void
{
    $job = new Job;
    $job->queue = 'default';
    $job->payload = '';
    $job->added_dt = $time;
    $job->send_dt = $time;
    $job->is_complete = true;
    $job->created_at = $time;
    $job->save();
}

function trimOptions(): array
{
    return [
        'trim' => [
            'completed' => 60,
            'failed' => 10080,
        ],
    ];
}

function supervisorOptions(): SupervisorOptions
{
    WorkerCommandString::$command = 'exec '.PHP_BINARY.' worker.php';

    return new SupervisorOptions(
        maxProcesses: 5,
        minProcesses: 2,
        directory: realpath(__DIR__.'/../'),
        balanceCooldown: 1,
        maxWorkload: 5,
    );
}