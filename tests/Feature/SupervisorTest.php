<?php

use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\QueueManager as Queue;
use Mach3queue\SuperVisor\Supervisor;
use Mach3queue\Supervisor\SupervisorRepository;
use Mach3queue\Supervisor\WorkerCommandString;
use Tests\Feature\Fakes\SupervisorWithFakeExit;

describe('Supervisor', function () {

    test('can be found in repository', function () {
        // setup
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        // run
        $supervisor->loop();

        // assert
        expect(SupervisorRepository::get($supervisor->name))->not->toBeNull();
    });

    test('can be terminated', function () {
        // setup
        $options = supervisorOptions();
        $supervisor = new SupervisorWithFakeExit($options);

        // run
        $supervisor->terminate();

        // assert
        expect($supervisor->exited)->toBeTrue()
            ->and(SupervisorRepository::get($supervisor->name))->toBeNull();
    });

    test('can start worker process', function () {
        // setup
        $supervisor = new Supervisor(supervisorOptions());

        // run
        $supervisor->scale(1);
        $supervisor->loop();

        // assert
        expect($supervisor->processes()[0]->getCommandLine())
            ->toContain(WorkerCommandString::$command);
    });

    test('can auto scale process pool down', function () {
        // setup
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        // run
        $supervisor->scale($options->maxProcesses);
        $supervisor->loop();

        advanceTimeBySeconds(1);

        $supervisor->loop();

        // assert
        expect($supervisor->processes()->count())->toBe(4);
    });

    test('can autoscale process pool up', function () {
        // setup
        addFakeJobToQueue(20);
        $options = supervisorOptions();
        $supervisor = new Supervisor($options);

        // run
        $supervisor->scale(2);
        advanceTimeBySeconds(1);
        $supervisor->loop();

        // assert
        expect($supervisor->processes()->count())->toBe(5);
    });

    test('can be restarted', function () {
        $supervisor = new Supervisor(supervisorOptions());

        $supervisor->scale(2);
        $supervisor->restart();

        expect($supervisor->terminatingProcesses())->toHaveCount(2)
            ->and($supervisor->processes())->toHaveCount(0);
    });

    test('can prune terminating processes', function () {
        $supervisor = new Supervisor(supervisorOptions());

        $supervisor->scale(2);
        $supervisor->restart();
        $supervisor->loop();

        expect($supervisor->terminatingProcesses())->toHaveCount(0);
    });

});