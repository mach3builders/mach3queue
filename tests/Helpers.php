<?php

use Mach3queue\Job\Job;
use Mockery\MockInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Mockery\LegacyMockInterface;
use Symfony\Component\Process\Process;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\WorkerCommandString;
use Mach3queue\Queue\QueueManager as Queue;

$default_supervisor_config = [
    'queue' => ['default'],
    'min_processes' => 2,
    'max_processes' => 5,
    'timeout' => 60,
    'directory' => realpath(__DIR__.'/../'),
];

function advanceTimeByMinutes(int $minutes): void
{
    advanceTimeBySeconds($minutes * 60);
}

function advanceTimeBySeconds(int $seconds): void
{
    $target = CarbonImmutable::now()->addSeconds($seconds);

    // The queue writes timestamps with Illuminate\Support\Carbon and reads
    // them back with Carbon\CarbonImmutable. Carbon 2 keeps a separate test
    // now per class, so setting only one leaves the two clocks disagreeing;
    // Carbon 3 shares it. Set both so the suite behaves the same on either.
    CarbonImmutable::setTestNow($target);
    Carbon::setTestNow($target);
}

/**
 * Move past the one second guard in Job::scopeSendBeforeNow so a freshly
 * added job becomes reservable.
 */
function advancePastSendGuard(): void
{
    advanceTimeBySeconds(2);
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
    $job->updated_at = $time;
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

function supervisorOptions(array $queues = ['default']): SupervisorOptions
{
    WorkerCommandString::$command = 'exec '.PHP_BINARY.' worker.php';

    return new SupervisorOptions(
        queues: $queues,
        maxProcesses: 5,
        minProcesses: 2,
        directory: realpath(__DIR__.'/../'),
        balanceCooldown: 1,
        maxWorkload: 5,
    );
}

function defaultSupervisorConfig1(
    array $queue = ['default'],
    int $min_processes = 2,
    int $max_processes = 5,
    int $timeout = 60,
    string $directory = __DIR__.'/'
): array {
    return [
        'supervisor-1' => [
            'queue' => $queue,
            'min_processes' => $min_processes,
            'max_processes' => $max_processes,
            'timeout' => $timeout,
            'directory' => $directory,
        ],
    ];
}

function fakeProcess(): Process|MockInterface|LegacyMockInterface
{
    return Mockery::mock(Process::class);
}

function createWorkProcess(): Process
{
    $options = supervisorOptions();
    $command = WorkerCommandString::fromOptions($options);

    return Process::fromShellCommandline($command, $options->directory)
        ->setTimeout(null)
        ->disableOutput();
}

function addFakeJobToQueue(int $count = 1): array|Job
{
    if ($count == 1) {
        return Queue::addJob(new FakeEmptyQueueable);
    }

    $jobs = [];

    for ($i = 0; $i < $count; $i++) {
        $jobs[] = Queue::addJob(new FakeEmptyQueueable);
    }

    return $jobs;
}