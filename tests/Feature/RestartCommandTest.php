<?php

use Symfony\Component\Process\Process;
use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;

describe('Restart command', function () {
    test('can restart all supervisors', function () {
        $master = new MasterSupervisor([]);
        $process = Mockery::mock(Process::class)->shouldIgnoreMissing();
        $process->expects()->signal(SIGUSR1)->once();

        $master->addSupervisorProcess(new SupervisorOptions, $process);
        $master->restart();
    });
});