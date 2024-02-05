<?php

use Mach3queue\Action\RunJob;
use Mach3queue\Job\Job;
use Mach3queue\Job\Status;
use Mach3queue\Queue\FakeEmptyQueueable;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function test_can_run(): void
    {
        $job = new Job;
        $job->payload = serialize(new FakeEmptyQueueable);

        (new RunJob)->execute($job);

        $this->assertTrue(true);
    }

    public function test_can_have_status_pending(): void
    {
        $job = new Job;
        $job->is_complete = 0;
        $job->is_reserved = 0;
        $job->attempts = 0;
        
        $this->assertEquals(Status::PENDING, $job->status());
    }

    public function test_can_have_status_processing(): void
    {
        $job = new Job;
        $job->is_complete = 0;
        $job->is_reserved = 1;
        
        $this->assertEquals(Status::PROCESSING, $job->status());
    }

    public function test_can_have_status_failed(): void
    {
        $job = new Job;
        $job->is_complete = 0;
        $job->is_reserved = 0;
        $job->attempts = 1;
        $job->is_buried = 1;
        
        $this->assertEquals(Status::FAILED, $job->status());
    }

    public function test_can_have_status_completed(): void
    {
        $job = new Job;
        $job->is_complete = 1;
        
        $this->assertEquals(Status::COMPLETED, $job->status());
    }
}