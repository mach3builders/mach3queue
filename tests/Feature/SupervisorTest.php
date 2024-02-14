<?php

use Mach3queue\SuperVisor\Supervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\WorkerCommandString;

describe('Supervisor', function () {
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
});

function supervisorOptions(): SupervisorOptions
{
    WorkerCommandString::$command = 'exec '.PHP_BINARY.' worker.php';
    return new SupervisorOptions(
        maxProcesses: 5,
    );
}