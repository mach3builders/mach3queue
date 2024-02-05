<?php

use Mach3queue\Queue\FakeEmptyQueueable;
use Mach3queue\Queue\QueueManager as Queue;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
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

    public function test_can_add_and_get_job(): void
    {
        $job_id = Queue::addJob(new FakeEmptyQueueable);

        $this->assertNotEmpty(Queue::get($job_id));
    }

    public function test_can_get_next_job(): void
    {
        Queue::addJob(new FakeEmptyQueueable);

        $this->assertNotEmpty(Queue::getNextJob());
    }

    public function test_can_delete_job(): void
    {
        $job_id = Queue::addJob(new FakeEmptyQueueable);

        Queue::deleteJob($job_id);

        $this->assertEmpty(Queue::getNextJob());
    }

    public function test_can_prioritize_job(): void
    {
        $job_id_1 = Queue::addJob(new FakeEmptyQueueable, 0, 10);
        $job_id_2 = Queue::addJob(new FakeEmptyQueueable, 0, 20);
        $job_id_3 = Queue::addJob(new FakeEmptyQueueable, 0, 30);

        $this->assertEquals($job_id_1, Queue::getNextJob()->id);
        $this->assertEquals($job_id_2, Queue::getNextJob()->id);
        $this->assertEquals($job_id_3, Queue::getNextJob()->id);
    }

    public function test_can_use_specific_queue(): void
    {
        Queue::on('queue_1')->addJob(new FakeEmptyQueueable);
        Queue::on('queue_2')->addJob(new FakeEmptyQueueable);

        $this->assertEquals('queue_1', Queue::on('queue_1')->getNextJob()->queue);
        $this->assertEquals('queue_2', Queue::on('queue_2')->getNextJob()->queue);
    }

    public function test_can_bury_job(): void
    {
        $job_id = Queue::addJob(new FakeEmptyQueueable);

        Queue::buryJob($job_id, 'test');
        
        $job = Queue::get($job_id);

        $this->assertEquals(1, $job->is_buried);
        $this->assertEquals('test', $job->message);
    }
}