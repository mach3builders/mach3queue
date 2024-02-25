<?php

namespace Mach3queue\Job;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class Job extends Model
{
    const TIMEOUT_MESSAGE = 'Job has timed out';
    
    public function scopeNextJobForPipelines(Builder $query, array $pipelines): void
    {
        $query->whereIn('queue', $pipelines)
            ->sendBeforeNow()
            ->isNotBuried()
            ->isNotComplete()
            ->isNotReservedOrReservedTimeExpired()
            ->isNotAttemptedOrTimeToRetryIsNow()
            ->orderBy('priority');
    }

    public function scopeSendBeforeNow(Builder $query): void
    {
        $query->where('send_dt', '<=', Carbon::now());
    }

    public function scopeIsNotBuried(Builder $query): void
    {
        $query->where('is_buried', 0);
    }

    public function scopeIsNotComplete(Builder $query): void
    {
        $query->where('is_complete', 0);
    }

    public function scopeIsNotReservedOrReservedTimeExpired(Builder $query): void
    {
        $query->where(function ($query) {
            $query->isNotReserved()->orWhere(function ($query) {
                $query->reservedIsExpired();
            });
        });
    }

    public function scopeIsNotReserved(Builder $query): void
    {
        $query->where('is_reserved', 0);
    }

    public function scopeReservedIsExpired(Builder $query): void
    {
        $query->where('is_reserved', 1)
            ->where('reserved_dt', '<=', Carbon::now()->subMinutes(5));
    }

    public function scopeIsNotAttemptedOrTimeToRetryIsNow(Builder $query): void
    {
        $query->where(function ($query) {
            $query->isNotAttempted()->orWhere(function ($query) {
                $query->timeToRetryIsNow();
            });
        });
    }

    public function scopeIsNotAttempted(Builder $query): void
    {
        $query->where('attempts', 0);
    }

    public function scopeTimeToRetryIsNow(Builder $query): void
    {
        $query->where('attempts', '>=', 1)
            ->where('time_to_retry_dt', '<=', Carbon::now());
    }

    public function scopeOlderThanSeconds(Builder $query, int $seconds): void
    {
        $query->where('created_at', '>', Carbon::now()->subSeconds($seconds));
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('is_complete', 1);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('is_buried', 1);
    }

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
}
