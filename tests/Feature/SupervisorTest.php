<?php

use Mach3queue\SuperVisor\Supervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\SupervisorRepository;
use Mach3queue\Supervisor\WorkerCommandString;
use Tests\Feature\Fakes\SupervisorWithFakeExit;

describe('Supervisor', function () {
    test('can be found in repository', function () {
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        $supervisor->loop();
        
        expect(SupervisorRepository::get($supervisor->name))->not->toBeNull();
    });

    test('can be terminated', function () {
        $options = supervisorOptions();
        $supervisor = new SupervisorWithFakeExit($options);

        $supervisor->terminate();
        
        expect($supervisor->exited)->toBeTrue();
        expect(SupervisorRepository::get($supervisor->name))->toBeNull();
    });

    test('can start worker process', function () {
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        $supervisor->scale(1);
        $supervisor->loop();
        
        expect($supervisor->processes()[0]->getCommandLine())
            ->toContain(WorkerCommandString::$command);
    });

    test('can scale process pool up', function () {
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        $supervisor->scale(2);
        $supervisor->loop();

        expect($supervisor->processes()->count())->toBe(2);
    });

    test('can scale process pool down', function () {
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        $supervisor->scale(2);
        $supervisor->loop();
        $supervisor->scale(1);
        $supervisor->loop();

        expect($supervisor->processes()->count())->toBe(1);
        expect($supervisor->terminatingProcesses()->count())->toBe(1);
    });

    // test('can auto scale process pool', function () {
    //     $options = supervisorOptions();
    //     $supervisor = new Supervisor($options);

    //     $supervisor->loop();

    //     expect($supervisor->processes()->count())->toBe(2);
    // });
});

function supervisorOptions(): SupervisorOptions
{
    WorkerCommandString::$command = 'exec '.PHP_BINARY.' worker.php';
    
    return new SupervisorOptions(
        maxProcesses: 5,
        minProcesses: 2,
        balanceCooldown: 1,
        directory: realpath(__DIR__.'/../'),
    );
}