<?php

use Carbon\CarbonImmutable;
use Mach3queue\Action\ExpireSupervisors;
use Mach3queue\Process\SupervisorProcess;
use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;
use Mach3queue\Supervisor\SupervisorRepository;
use Symfony\Component\Process\Process;
use Tests\Feature\Fakes\MasterSupervisorWithFakeExit;

describe('Master Supervisor', function () {
    test('Has a name', function () {
        $name = MasterSupervisor::name();
        
        expect($name)->toBeString()
            ->and($name)->not->toBeEmpty();
    });

    test('can add a new supervisor process', function () {
        $master = new MasterSupervisor([]);
        $process = Mockery::mock(Process::class);
        $master->addSupervisorProcess(new SupervisorOptions, $process);

        expect($master->supervisors)->toHaveCount(1)
            ->and($master->supervisors[0])->toBeInstanceOf(SupervisorProcess::class);
    });

    test('Can clean up dead supervisor process', function () {
        $master = new MasterSupervisor([]);
        $process = Mockery::mock(Process::class);
        $master->addSupervisorProcess(new SupervisorOptions, $process);
        $supervisor_process = $master->supervisors[0];

        $process->shouldReceive('isStarted')->andReturn(true);
        $process->shouldReceive('isRunning')->andReturn(false);

        $master->loop();

        expect($supervisor_process->dead)->toBeTrue()
            ->and($master->supervisors)->toHaveCount(0);
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

    test('Can be found in repository', function() {
        $master = new MasterSupervisor([]);
        $master->loop();

        $supervisor = SupervisorRepository::get(MasterSupervisor::name());

        expect($supervisor->name)->toBe(MasterSupervisor::name())
            ->and($supervisor->status)->toBe('running');
    });

    test('Can terminate', function () {
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

        $master = new MasterSupervisorWithFakeExit($config);
        $master->loop();
        $master->terminate();

        expect($master->exited)->toBeTrue()
            ->and(SupervisorRepository::get(MasterSupervisor::name()))->toBeEmpty();
    });

    test('Can get longest running supervisor', function() {
        $config = [
            'supervisor-1' => [
                'queue' => ['default'],
                'max_processes' => 1,
                'timeout' => 300,
                'directory' => realpath(__DIR__.'/../'),
            ],
        ];

        $master = new MasterSupervisor($config);
        $longest = $master->getLongestTimeoutSupervisor();

        expect($longest)->toBe(300);
    });

    test('Can persist in the repository', function () {
        $master = new MasterSupervisor([]);

        $master->loop();

        $date_1 = SupervisorRepository::get(MasterSupervisor::name())->updated_at;

        advanceTimeByMinutes(2);

        $master->loop();

        $date_2 = SupervisorRepository::get(MasterSupervisor::name())->updated_at;

        expect($date_1)->not->toBe($date_2);
    });

    test('Can remove expired supervisors', function () {
        $master = new MasterSupervisor([]);

        $master->loop();

        advanceTimeByMinutes(2);

        (new ExpireSupervisors())();

        expect(SupervisorRepository::get(MasterSupervisor::name()))->toBeEmpty();
    });
});

function advanceTimeByMinutes(int $minutes): void
{
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinutes($minutes));
}