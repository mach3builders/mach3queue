<?php

use Mach3queue\Process\SupervisorProcess;
use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\SuperVisor\Supervisor;
use Mach3queue\Supervisor\SupervisorCommandString;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\SupervisorRepository;
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
        SupervisorCommandString::$command = 'exec '.PHP_BINARY.' supervisor.php';

        $config = [
            'supervisor-1' => [
                'queue' => ['default'],
                'maxProcesses' => 5,
                'timeout' => 60,
                'directory' => realpath(__DIR__.'/../'),
            ],
            'supervisor-2' => [
                'queue' => ['ai', 'export'],
                'maxProcesses' => 3,
                'timeout' => 60,
                'directory' => realpath(__DIR__.'/../'),
            ],
        ];

        $master = new MasterSupervisor($config);
        $master->loop();
        
        expect($master->supervisors)->toHaveCount(2);
        expect(SupervisorRepository::get('supervisor-1')->processes)->toBe(5);
        expect(SupervisorRepository::get('supervisor-2')->processes)->toBe(3);
    });
});