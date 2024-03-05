<?php

use Mach3queue\Dashboard\Dashboard;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Supervisor\MasterSupervisor;

describe('Dashboard', function () {

    test('can get dashboard data', function () {
        // setup
        $master = new MasterSupervisor([
            ...trimOptions(),
            'supervisors' => [...defaultSupervisorConfig1()]
        ]);
        addFakeJobToQueue(5);

        // run
        $master->loop();

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'dashboard']));

        expect($data->active)->toBeTrue()
            ->and($data->supervisors)->toHaveCount(0)
            ->and($data->completedJobs)->toBe(0)
            ->and($data->failedJobs)->toBe(0)
            ->and($data->pendingJobs)->toBe(5)
            ->and($data->queues[0]->count)->toBe(5);
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

        foreach($jobs as $job) {
            $job->is_complete = true;
            $job->save();
        }

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'completed']));

        expect($data)->toHaveCount(5)
            ->and($data[0]->name)->toBeString()
            ->and($data[0]->tags)->toBeArray()
            ->and($data[0]->tags[0]->name)->toBe('test')
            ->and($data[0]->tags[0]->value)->toBe(10)
            ->and($data[0]->runtime)->toBeFloat();
    });

    test('can get failed jobs', function () {
        // setup
        $jobs = addFakeJobToQueue(5);

        foreach($jobs as $job) {
            $job->is_buried = true;
            $job->save();
        }

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'failed']));

        expect($data)->toHaveCount(5)
            ->and($data[0]->name)->toBeString()
            ->and($data[0]->tags)->toBeArray()
            ->and($data[0]->tags[0]->name)->toBe('test')
            ->and($data[0]->tags[0]->value)->toBe(10)
            ->and($data[0]->runtime)->toBeFloat();
    });
});