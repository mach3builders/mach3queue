<?php

use Mach3queue\Action\KillWorker;
use Mach3queue\Job\Job;
use Mach3queue\Job\Status;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\FakeSleepQueueable;
use Mach3queue\Queue\QueueManager as Queue;
use Mach3queue\Worker\Worker;
use Mach3queue\Worker\WorkerActions;
use Mach3queue\Worker\WorkerOptions;

describe('Worker', function () {
    test('can timeout', function () {
        $job_timer = 3;
        $timeout = 1;

        Queue::addJob(new FakeSleepQueueable($job_timer));

        // start the session timer
        $_SESSION["start_time"] = time();
        $queue = $this->queue->getInstance();
        $action = Mockery::mock(KillWorker::class);
        $actions = new WorkerActions(killWorker: $action);
        $options = new WorkerOptions(stop_when_empty: true);

        $action->shouldReceive('execute')->andReturn($_SESSION["start_end"] = time());

        // run a worker
        $worker = new Worker($queue, $timeout, $actions, $options);
        $worker->run();

        $job = Job::first();

        // check if the session timer is less than the timeout
        expect($_SESSION["start_time"] - $_SESSION["start_end"])->toBeLessThan($job_timer);
    });

    test('can mark job as complete', function () {
        Queue::addJob(new FakeEmptyQueueable);
        $queue = $this->queue->getInstance();
        $actions = new WorkerActions;
        $options = new WorkerOptions(stop_when_empty: true);

        // run a worker
        $worker = new Worker($queue, 60, $actions, $options);
        $worker->run();

        $job = Job::first();

        expect($job->status())->toBe(Status::COMPLETED);
    });
});