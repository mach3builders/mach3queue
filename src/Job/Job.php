<?php

namespace Mach3queue\Job;

use Illuminate\Database\Eloquent\Model;


class Job extends Model
{
    public function status(): Status
    {
        if ($this->is_complete == 1) {
            return Status::COMPLETED;
        }

        if ($this->is_reserved == 1) {
            return Status::PROCESSING;
        }

        if ($this->attempts == 0) {
            return Status::PENDING;
        }

        if ($this->is_buried == 1) {
            return Status::FAILED;
        }
    }

    public function timedOut(): void
    {
        
    }

    public function run(): void
    {
        $object = unserialize($this->payload);
        $object->handle();
    }
}
