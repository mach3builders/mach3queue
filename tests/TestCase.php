<?php

namespace Tests;

use Mach3queue\Queue\QueueManager as Queue;
use Mach3queue\Supervisor\SupervisorOptions;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private Queue $queue;
}
