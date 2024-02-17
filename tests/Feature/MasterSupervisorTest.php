<?php

use Mach3queue\Process\SupervisorProcess;
use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Symfony\Component\Process\Process;

describe('Master Supervisor', function () {
    test('Has a name', function () {
        $name = MasterSupervisor::name();
        
        expect($name)->toBeString();
        expect($name)->not->toBeEmpty();
    });

    test('can add a new supervisor process', function () {
        $master = new MasterSupervisor([]);
        $process = Mockery::mock(Process::class);
        $master->addSupervisorProcess(new SupervisorOptions, $process);

        expect($master->supervisors)->toHaveCount(1);
        expect($master->supervisors[0])->toBeInstanceOf(SupervisorProcess::class);
    });

    test('Can clean up dead supervisor process', function () {
        $master = new MasterSupervisor([]);
        $process = Mockery::mock(Process::class);
        $master->addSupervisorProcess(new SupervisorOptions, $process);
        $supervisor_process = $master->supervisors[0];

        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(false);

        $master->loop();

        expect($supervisor_process->dead)->toBeTrue();
        expect($master->supervisors)->toHaveCount(0);
    });

    test('Can create supervisors based on a config', function() {
        $config = [
            'supervisor-1' => [
                'queue' => ['default'],
                'max_processes' => 5,
                'timeout' => 60,
                'directory' => realpath(__DIR__.'/../'),
            ],
            'supervisor-2' => [
                'queue' => ['ai', 'export'],
                'max_processes' => 3,
                'timeout' => 60,
                'directory' => realpath(__DIR__.'/../'),
            ],
        ];

        $master = new MasterSupervisor($config);

        expect($master->supervisors)->toHaveCount(2)
        ->and($master->supervisors[0]->process->getCommandLine())
            ->toContain('mach3 queue:supervisor')
            ->toContain('--max-processes=5')
            ->toContain('--queue=default')
            ->toContain('--master='.MasterSupervisor::name())
            ->toContain('--directory='.realpath(__DIR__.'/../'))
        ->and($master->supervisors[1]->process->getCommandLine())
            ->toContain('mach3 queue:supervisor')
            ->toContain('--max-processes=3')
            ->toContain('--queue=ai,export')
            ->toContain('--master='.MasterSupervisor::name())
            ->toContain('--directory='.realpath(__DIR__.'/../'));
    });
});