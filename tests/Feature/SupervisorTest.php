<?php

use Carbon\CarbonImmutable;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\QueueManager as Queue;
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
        
        expect($supervisor->exited)->toBeTrue()
            ->and(SupervisorRepository::get($supervisor->name))->toBeNull();
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

    test('can auto scale process pool down', function () {
         $options = supervisorOptions();
         $supervisor = new Supervisor($options);

         $supervisor->scale($options->maxProcesses);
         $supervisor->loop();

         advanceTimeBySeconds(1);

         $supervisor->loop();

         expect($supervisor->processes()->count())->toBe(4);
     });

     test('can autoscale process pool up', function () {
         $options = supervisorOptions();
         $supervisor = new Supervisor($options);

         $supervisor->scale(2);
         $supervisor->loop();

         for ($i = 0; $i < 20; $i++) {
            Queue::addJob(new FakeEmptyQueueable);
         }

         advanceTimeBySeconds(1);
         $supervisor->loop();

         expect($supervisor->processes()->count())->toBe(3);
     });
});

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

function advanceTimeByMinutes(int $minutes): void
{
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinutes($minutes));
}

function advanceTimeBySeconds(int $seconds): void
{
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addSeconds($seconds));
}