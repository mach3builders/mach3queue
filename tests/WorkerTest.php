<?php

use Mach3queue\Action\FakeAction;
use Mach3queue\Queue\FakeSleepQueueable;
use Mach3queue\Queue\QueueManager as Queue;
use Mach3queue\Worker\Worker;
use Mach3queue\Worker\WorkerActions;
use Mach3queue\Worker\WorkerOptions;
use PHPUnit\Framework\TestCase;

class WorkerTest extends TestCase
{
    protected Queue $queue;

    public function setUp(): void {
		$this->queue = new Queue;
        $this->queue->setConnection([
            'driver' => 'sqlite',
            'host' => 'localhost',
            'database' => ':memory:',
            'username' => 'test',
            'password' => 'test',
        ]);
        $this->queue->setAsGlobal();
	}

    public function test_can_timeout(): void
    {
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
        $this->assertTrue($_SESSION["start_time"] - $_SESSION["start_end"] < $job_timer);
    }
}