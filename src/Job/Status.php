<?php

namespace Mach3queue\Job;

enum Status: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
    case COMPLETED = 'completed';
    case STOPPED = 'stopped';
}
