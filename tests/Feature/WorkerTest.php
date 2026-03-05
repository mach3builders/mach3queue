<?php

use Mach3queue\Action\BuryJob;
use Mach3queue\Action\KillWorker;
use Mach3queue\Job\Job;
use Mach3queue\Job\Status;
use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\FakeSleepQueueable;
use Mach3queue\Queue\QueueManager as Queue;
use Mach3queue\Worker\Worker;
use Mach3queue\Worker\WorkerActions;
use Mach3queue\Worker\WorkerOptions;
use Tests\Feature\Fakes\RunJobWithException;

describe('Worker', function () {

    test('can timeout', function () {
        $job_timer = 3;
        Queue::addJob(new FakeSleepQueueable($job_timer));
        $_SESSION["start_time"] = time();
        $action = Mockery::mock(KillWorker::class);
        $worker = new Worker(
            queue: $this->queue->getInstance(),
            timeout: 1,
            actions: new WorkerActions(killWorker: $action),
            options: new WorkerOptions(stop_when_empty: true)
        );

        // test
        $action->expects()->__invoke()->andReturnUsing(
            fn () => $_SESSION["start_end"] = time()
        );

        // run
        $worker->run();

        // check if the session timer is less than the timeout
        expect($_SESSION["start_time"] - $_SESSION["start_end"])->toBeLessThan($job_timer);
    });

    test('can mark job as complete', function () {
        // setup
        addFakeJobToQueue();
        $queue = $this->queue->getInstance();
        $actions = new WorkerActions;
        $options = new WorkerOptions(stop_when_empty: true);

        // run
        $worker = new Worker($queue, 60, $actions, $options);
        $worker->run();

        // assert
        expect(Job::first()->status())->toBe(Status::COMPLETED);
    });

    test('can run out of memory', function() {
        // setup
        $queue = $this->queue->getInstance();
        $options = new WorkerOptions(
            stop_when_empty: true,
            memory: 1
        );
        $worker = new Worker($queue, 60, new WorkerActions, $options);

        // run
        $worker->run();

        // assert
        expect($worker->run())->toBe(Worker::$EXIT_MEMORY_LIMIT);
    });

    test('can be terminated', function () {
        // setup
        $queue = $this->queue->getInstance();
        $options = new WorkerOptions;
        $worker = new Worker($queue, 60, new WorkerActions, $options);

        // run
        $worker->terminate();

        // assert
        expect($worker->run())->toBe(Worker::$EXIT_ERROR);
    });

    test('can be paused', function () {
        // setup
        $worker = new Worker($this->queue->getInstance());

        // run
        $worker->pause();

        // assert
        expect($worker->working)->toBeFalse();
    });

    test('can be resumed', function () {
        // setup
        $worker = new Worker($this->queue->getInstance());

        // run
        $worker->pause();
        $worker->resume();

        // assert
        expect($worker->working)->toBeTrue();
    });

    test('can bury job when it fails', function () {
        // setup
        $queue = $this->queue->getInstance();
        $job = addFakeJobToQueue();
        $actions = new WorkerActions(runJob: new RunJobWithException);
        $options = new WorkerOptions(stop_when_empty: true);
        $worker = new Worker($queue, 60, $actions, $options);

        // run
        $worker->run();

        // assert
        expect($job->refresh()->status())->toBe(Status::FAILED);
    });

    test('can call callable after job when finished', function () {
        // setup
        Queue::addJob(new FakeEmptyQueueable)
            ->after(function (Job $job) {
                $job->message = 'after called successfully';
                $job->save();
            });

        $queue = $this->queue->getInstance();
        $actions = new WorkerActions;
        $options = new WorkerOptions(stop_when_empty: true);

        // run
        $worker = new Worker($queue, 60, $actions, $options);
        $worker->run();

        // assert
        expect(Job::first()->message)->toBe('after called successfully');
    });

    test('will run after callable only when max attempts when failed', function () {
        $job = Queue::addJob(new FakeEmptyQueueable)
            ->after(function (Job $job) {
                $job->message = 'after called successfully';
                $job->save();
            });
        $job->attempts = 2;
        $job->save();

        (new BuryJob)($job, 'failed', 0, 3);

        expect(Job::first()->message)->not->toBe('after called successfully');

        (new BuryJob)($job, 'failed', 0, 2);

        expect(Job::first()->message)->toBe('after called successfully');
    });

    test('does not crash when after callback throws during bury', function () {
        $job = Queue::addJob(new FakeEmptyQueueable)
            ->after(function (Job $job) {
                throw new \RuntimeException('callback failed');
            });
        $job->attempts = 3;
        $job->save();

        (new BuryJob)($job, 'failed', 0, 3);

        expect($job->refresh()->is_buried)->toBe(1);
    });

    test('does not crash when after callback throws during complete', function () {
        $job = Queue::addJob(new FakeEmptyQueueable)
            ->after(function (Job $job) {
                throw new \RuntimeException('callback failed');
            });

        (new \Mach3queue\Action\CompleteJob)($job);

        expect($job->refresh()->is_complete)->toBe(1);
    });

    test('calls before_job callback before each job', function () {
        $job = addFakeJobToQueue();
        $called = false;

        $queue = $this->queue->getInstance();
        $worker = new Worker($queue);
        $worker->beforeJob(function (Job $job) use (&$called) {
            $called = true;
        });

        $runJob = new \ReflectionMethod($worker, 'runJob');
        $runJob->invoke($worker, $job);

        expect($called)->toBeTrue();
    });

    test('before_job callback receives the job', function () {
        $addedJob = addFakeJobToQueue();
        $receivedJobId = null;

        $queue = $this->queue->getInstance();
        $worker = new Worker($queue);
        $worker->beforeJob(function (Job $job) use (&$receivedJobId) {
            $receivedJobId = $job->id;
        });

        $runJob = new \ReflectionMethod($worker, 'runJob');
        $runJob->invoke($worker, $addedJob);

        expect($receivedJobId)->toBe($addedJob->id);
    });
});