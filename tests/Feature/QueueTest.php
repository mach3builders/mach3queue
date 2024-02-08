<?php

use Mach3queue\Action\BuryJob;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\QueueManager as Queue;


describe('Queue', function () {
    test('can add job', function () {
        $job = Queue::addJob(new FakeEmptyQueueable);

        expect($job)->not->toBeEmpty();
    });

    test('can get next job', function () {
        Queue::addJob(new FakeEmptyQueueable);

        expect(Queue::getNextJob())->not->toBeEmpty();
    });

    test('can delete job', function () {
        $job = Queue::addJob(new FakeEmptyQueueable);

        Queue::deleteJob($job->id);

        expect(Queue::getNextJob())->toBeEmpty();
    });

    test('can use specific queue', function () {
        Queue::on('queue_1')->addJob(new FakeEmptyQueueable);
        Queue::on('queue_2')->addJob(new FakeEmptyQueueable);

        expect(Queue::on('queue_1')->getNextJob()->queue)->toBe('queue_1');
        expect(Queue::on('queue_2')->getNextJob()->queue)->toBe('queue_2');
    });

    test('can prioritize job', function () {
        $job_1 = Queue::addJob(new FakeEmptyQueueable, 0, 30);
        $job_2 = Queue::addJob(new FakeEmptyQueueable, 0, 20);
        $job_3 = Queue::addJob(new FakeEmptyQueueable, 0, 10);

        expect($job_3->id)->toBe(Queue::getNextJob()->id);
        expect($job_2->id)->toBe(Queue::getNextJob()->id);
        expect($job_1->id)->toBe(Queue::getNextJob()->id);
    });

    test('can bury job', function () {
        $job = Queue::addJob(new FakeEmptyQueueable);
        
        (new BuryJob)->execute($job, 'test');
        
        $job->refresh();

        expect($job->is_buried)->toBe(1);
        expect($job->message)->toBe('test');
    });
});