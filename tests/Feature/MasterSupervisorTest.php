<?php

use Mach3queue\Job\Job;
use Carbon\CarbonImmutable;
use Mach3queue\Supervisor\Supervisor;
use Mach3queue\Action\ExpireSupervisors;
use Mach3queue\Process\SupervisorProcess;
use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorRepository;
use Tests\Feature\Fakes\MasterSupervisorWithFakeExit;

describe('Master Supervisor', function () {

    test('has a name', function () {
        // assert
        expect(MasterSupervisor::name())->toBeString();
    });

    test('can add a new supervisor process', function () {
        // setup
        $master = new MasterSupervisor([]);
        $master->addSupervisorProcess(supervisorOptions(), fakeProcess());
        $supervisors = $master->supervisors;

        // assert
        expect($supervisors)->toHaveCount(1)
            ->and($supervisors->first())->toBeInstanceOf(SupervisorProcess::class);
    });

    test('can clean up dead supervisor process', function () {
        // setup
        $master = new MasterSupervisor(trimOptions());
        $process = fakeProcess();
        $master->addSupervisorProcess(supervisorOptions(), $process);
        $supervisor = $master->supervisors->first();

        // test
        $process->expects()->isStarted()->andReturn(true);
        $process->expects()->isRunning()->andReturn(false);

        // run
        $master->loop();

        // assert
        expect($supervisor->dead)->toBeTrue()
            ->and($master->supervisors)->toHaveCount(0);
    });

    test('can create supervisor based on a config', function() {
        // setup
        $master = new MasterSupervisor([
            'supervisors' => [...defaultSupervisorConfig1()]
        ]);

        // assert
        expect($master->supervisors)->toHaveCount(1)
            ->and($master->supervisors[0]->process->getCommandLine())
            ->toContain('queue supervisor')
            ->toContain('--name=supervisor-1')
            ->toContain('--min-processes=2')
            ->toContain('--max-processes=5')
            ->toContain('--queue=default')
            ->toContain('--timeout=60')
            ->toContain('--master='.MasterSupervisor::name())
            ->toContain('--directory='.realpath(__DIR__.'/../'));
    });

    test('can be found in repository', function() {
        // setup
        $name = MasterSupervisor::name();
        $master = new MasterSupervisor(trimOptions());

        // run
        $master->loop();

        // assert
        expect(SupervisorRepository::get($name)->name)->toBe($name)
            ->and(SupervisorRepository::get($name)->status)->toBe('running');
    });

    test('Can terminate', function () {
        // setup
        $master = new MasterSupervisorWithFakeExit([
            ...trimOptions(),
            'supervisors' => [...defaultSupervisorConfig1()],
        ]);

        // run
        $master->loop();
        $master->terminate();

        // assert
        expect($master->exited)->toBeTrue()
            ->and(SupervisorRepository::get(MasterSupervisor::name()))->toBeEmpty();
    });

    test('Can get longest running supervisor', function() {
        // setup
        $master = new MasterSupervisor([
            ...trimOptions(),
            'supervisors' => [...defaultSupervisorConfig1(timeout: 300)],
        ]);

        // assert
        expect($master->getLongestTimeoutSupervisor())->toBe(300);
    });

    test('Can persist in the repository', function () {
        // setup
        $master = new MasterSupervisor(trimOptions());
        $name = MasterSupervisor::name();

        // run
        $master->loop();
        $date_1 = SupervisorRepository::get($name)->updated_at;

        advanceTimeByMinutes(2);

        $master->loop();
        $date_2 = SupervisorRepository::get($name)->updated_at;

        // assert
        expect($date_1)->not->toBe($date_2);
    });

    test('Can remove expired supervisors', function () {
        // setup
        $master = new MasterSupervisor(trimOptions());
        $supervisor = new Supervisor(supervisorOptions());

        // run
        $master->loop();
        $supervisor->loop();

        advanceTimeByMinutes(2);

        (new ExpireSupervisors())();

        // assert
        expect(SupervisorRepository::get($master->name))->toBeEmpty()
            ->and(SupervisorRepository::get($supervisor->name))->toBeEmpty();
    });

    test('Can trim old jobs', function () {
        // setup
        createCompletedJobAtTime(CarbonImmutable::now());
        createCompletedJobAtTime(CarbonImmutable::now()->subHours(25));
        $master = new MasterSupervisor(trimOptions());

        // run
        $master->loop();

        // assert
        expect(Job::count())->toBe(1);
    });
});