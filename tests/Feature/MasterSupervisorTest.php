<?php

use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\SupervisorProcess;
use Symfony\Component\Process\Process;

describe('Master Supervisor', function () {
    test('can add a new supervisor process', function () {
        $master = new MasterSupervisor;
        $process = Mockery::mock(Process::class);
        $master->addSupervisorProcess(new SupervisorOptions, $process);

        expect($master->supervisors)->toHaveCount(1);
        expect($master->supervisors[0])->toBeInstanceOf(SupervisorProcess::class);
    });

    test('Can clean up dead supervisor process', function () {
        $master = new MasterSupervisor;
        $process = Mockery::mock(Process::class);
        $master->addSupervisorProcess(new SupervisorOptions, $process);
        $supervisor_process = $master->supervisors[0];

        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(false);

        $master->loop();

        expect($supervisor_process->dead)->toBeTrue();
        expect($master->supervisors)->toHaveCount(0);
    });
});