<?php

use Mach3queue\Action\RunJob;
use Mach3queue\Job\Job;
use Mach3queue\Job\Status;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\QueueManager as Queue;


describe('Job', function () {
    test('can run', function () {
        $job = new Job;
        $job->payload = serialize(new FakeEmptyQueueable);
    
        (new RunJob)->execute($job);
    
        expect(true)->toBeTrue();
    });
    
    test('can have status pending', function () {
        $job = new Job;
        $job->is_complete = 0;
        $job->is_reserved = 0;
        $job->attempts = 0;
    
        expect($job->status())->toBe(Status::PENDING);
    });
    
    test('can have status processing', function () {
        $job = new Job;
        $job->is_complete = 0;
        $job->is_reserved = 1;
    
        expect($job->status())->toBe(Status::PROCESSING);
    });
    
    test('can have status failed', function () {
        $job = new Job;
        $job->is_complete = 0;
        $job->is_reserved = 0;
        $job->attempts = 1;
        $job->is_buried = 1;
    
        expect($job->status())->toBe(Status::FAILED);
    });
    
    test('can have status completed', function () {
        $job = new Job;
        $job->is_complete = 1;
    
        expect($job->status())->toBe(Status::COMPLETED);
    });
});