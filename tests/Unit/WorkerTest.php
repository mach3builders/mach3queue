<?php

use Mach3queue\Action\FakeAction;
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
        $action = new FakeAction(fn() => $_SESSION["start_end"] = time());
        $actions = new WorkerActions(kill_worker: $action);
        $options = new WorkerOptions(stop_when_empty: true);

        // run a worker
        $worker = new Worker($queue, $timeout, $actions, $options);
        $worker->run();

        // check if the session timer is less than the timeout
        expect($_SESSION["start_time"] - $_SESSION["start_end"])->toBeLessThan($job_timer);
    });
});