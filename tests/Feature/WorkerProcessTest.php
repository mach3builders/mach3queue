<?php

namespace Tests\Feature;

use Mach3queue\Process\WorkerProcess;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\WorkerCommandString;
use Symfony\Component\Process\Process;

describe('Worker Process', function () {
    test('can read last output time', function () {
        $worker_process = new WorkerProcess(createProcess());
        $worker_process->start(fn() => null);

        expect($worker_process->getIdleTime())->toBeLessThan(1);
        sleep(1);
        expect($worker_process->getIdleTime())->toBeGreaterThanOrEqual(1);
    });
});

function createProcess(): Process
{
    $options = supervisorOptions();
    $command = WorkerCommandString::fromOptions($options);

    return Process::fromShellCommandline($command, $options->directory)
        ->setTimeout(null)
        ->disableOutput();
}

function supervisorOptions(): SupervisorOptions
{
    WorkerCommandString::$command = 'exec '.PHP_BINARY.' worker.php';

    return new SupervisorOptions(
        maxProcesses: 5,
        minProcesses: 2,
        directory: realpath(__DIR__.'/../'),
        balanceCooldown: 1,
    );
}