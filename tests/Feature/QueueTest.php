<?php

use Carbon\CarbonImmutable;
use Mach3queue\Action\BuryJob;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\QueueManager as Queue;


describe('Queue', function () {

    test('can add job', function () {
        // setup
        $job = Queue::addJob(new FakeEmptyQueueable);

        // assert
        expect($job)->not->toBeEmpty();
    });

    test('can get next job', function () {
        // setup
        addFakeJobToQueue();

        // assert
        expect(Queue::getNextJob())->not->toBeEmpty();
    });

    test('can delete job', function () {
        // setup
        $job = addFakeJobToQueue();

        // run
        Queue::deleteJob($job->id);

        // assert
        expect(Queue::getNextJob())->toBeEmpty();
    });

    test('can use specific queue', function () {
        // setup
        Queue::on('queue_1')->addJob(new FakeEmptyQueueable);
        Queue::on('queue_2')->addJob(new FakeEmptyQueueable);

        // assert
        expect(Queue::on('queue_1')->getNextJob()->queue)->toBe('queue_1')
            ->and(Queue::on('queue_2')->getNextJob()->queue)->toBe('queue_2');
    });

    test('can get jobs for specific pipelines', function () {
        // setup
        Queue::on('queue_1')->addJob(new FakeEmptyQueueable);
        Queue::on('queue_2')->addJob(new FakeEmptyQueueable);
        Queue::pipelines(['queue_1', 'queue_2']);

        // assert
        expect(Queue::getNextJob()->queue)->toBe('queue_1')
            ->and(Queue::getNextJob()->queue)->toBe('queue_2');
    });

    test('can prioritize job', function () {
        // setup
        $job_1 = Queue::addJob(new FakeEmptyQueueable, 0, 30);
        $job_2 = Queue::addJob(new FakeEmptyQueueable, 0, 20);
        $job_3 = Queue::addJob(new FakeEmptyQueueable, 0, 10);

        // assert
        expect($job_3->id)->toBe(Queue::getNextJob()->id)
            ->and($job_2->id)->toBe(Queue::getNextJob()->id)
            ->and($job_1->id)->toBe(Queue::getNextJob()->id);
    });

    test('can bury job', function () {
        // setup
        $job = addFakeJobToQueue();

        // run
        (new BuryJob)($job, 'test', 60);

        $job->refresh();

        // assert
        expect($job->is_buried)->toBe(1)
            ->and($job->message)->toBe('test');
    });

    test('can limit the maximum tries', function () {
        // setup
        Queue::manager()->getInstance()->maxRetries = 3;
        $job = Queue::addJob(new FakeEmptyQueueable);
        $job->attempts = 3;
        $job->time_to_retry_dt = CarbonImmutable::now()->subSecond();
        $job->save();

        expect(Queue::getNextJob())->toBeEmpty();

        $job->attempts = 2;
        $job->save();

        expect(Queue::getNextJob())->toBeObject();
    });

    test('can retry a failed job after retry timer', function () {
        $job = Queue::addJob(new FakeEmptyQueueable);

        $job->time_to_retry_dt = CarbonImmutable::now()->subSeconds(60);
        $job->buried_dt = CarbonImmutable::now();
        $job->is_buried = true;
        $job->save();

        expect(Queue::getNextJob())->toBeObject();
    });
});