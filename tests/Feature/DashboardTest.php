<?php

use Mach3queue\Action\RunJob;
use Mach3queue\Action\BuryJob;
use Mach3queue\Action\CompleteJob;
use Mach3queue\Dashboard\Dashboard;
use Mach3queue\Supervisor\Supervisor;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Queue\QueueManager as Queue;

describe('Dashboard', function () {

    test('can get dashboard data', function () {
        // setup
        $master = new MasterSupervisor([...trimOptions()]);
        $supervisor = new Supervisor(supervisorOptions());
        addFakeJobToQueue(5);

        // run
        $master->loop();
        $supervisor->loop();

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'dashboard']));

        expect($data->active)->toBeTrue()
            ->and($data->supervisors)->toHaveCount(1)
            ->and($data->completedJobs)->toBe(0)
            ->and($data->failedJobs)->toBe(0)
            ->and($data->pendingJobs)->toBe(5)
            ->and($data->queues[0]->count)->toBe(5);
    });

    test('can get queue workload for multiple queues', function () {
        // setup
        $master = new MasterSupervisor([...trimOptions()]);
        $supervisor = new Supervisor(supervisorOptions(queues: ['default', 'emails']));

        Queue::on('default')->addJob(new FakeEmptyQueueable);
        Queue::on('default')->addJob(new FakeEmptyQueueable);
        Queue::on('emails')->addJob(new FakeEmptyQueueable);

        // run
        $master->loop();
        $supervisor->loop();

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'dashboard']));
        $queues = collect($data->queues)->keyBy('name');

        expect($queues)->toHaveCount(2)
            ->and($queues['default']->count)->toBe(2)
            ->and($queues['emails']->count)->toBe(1);
    });

    test('can get pending jobs', function () {
        // setup
        addFakeJobToQueue(5);

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'pending']));

        expect($data)->toHaveCount(5)
            ->and($data[0]->tags)->toBeArray()
            ->and($data[0]->tags[0]->name)->toBe('test')
            ->and($data[0]->tags[0]->value)->toBe(10)
            ->and($data[0]->name)->toBe(FakeEmptyQueueable::class);
    });

    test('can get completed jobs', function () {
        // setup
        $jobs = addFakeJobToQueue(5);


        // run
        foreach($jobs as $job) {
            (new RunJob())($job);
        }

        sleep(1);

        foreach($jobs as $job) {
            (new CompleteJob())($job);
        }

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'completed']));

        expect($data)->toHaveCount(5)
            ->and($data[0]->name)->toBeString()
            ->and($data[0]->tags)->toBeArray()
            ->and($data[0]->tags[0]->name)->toBe('test')
            ->and($data[0]->tags[0]->value)->toBe(10)
            ->and((float) $data[0]->runtime)->toBeGreaterThan(0.9);
    });

    test('can get failed jobs', function () {
        // setup
        $jobs = addFakeJobToQueue(5);

        // run
        foreach($jobs as $job) {
            (new RunJob())($job);
        }

        sleep(1);

        foreach($jobs as $job) {
            (new BuryJob())($job, 'failed', 0);
        }

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'failed']));

        expect($data)->toHaveCount(5)
            ->and($data[0]->name)->toBeString()
            ->and($data[0]->tags)->toBeArray()
            ->and($data[0]->tags[0]->name)->toBe('test')
            ->and($data[0]->tags[0]->value)->toBe(10)
            ->and((float) $data[0]->runtime)->toBeGreaterThan(0.9);
    });
});