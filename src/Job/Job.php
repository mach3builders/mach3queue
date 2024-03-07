<?php

namespace Mach3queue\Job;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method static nextJobForPipeLines(array|string[] $getPipelines)
 * @method static where(string $string, int $id)
 * @method static olderThanSeconds(int $completed_seconds)
 * @method static completed()
 * @method static failed()
 * @method static running()
 * @method static pending()
 * @method static queuesWorkload()
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property string $message
 * @property int $priority
 * @property int $is_buried
 * @property int $is_reserved
 * @property int $is_complete
 * @property int $attempts
 * @property Carbon $added_dt
 * @property Carbon $send_dt
 * @property Carbon $time_to_retry_dt
 * @property Carbon $buried_dt
 * @property Carbon $reserved_dt
 * @property Carbon $complete_dt
 * @property float $runtime
 */
class Job extends Model
{
    const string TIMEOUT_MESSAGE = 'Job has timed out';
    const string MEMORY_EXCEEDED_MESSAGE = 'Job memory limit exceeded';
    
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
        $query->where('send_dt', '<=', CarbonImmutable::now());
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
            ->where('reserved_dt', '<=', CarbonImmutable::now()->subMinutes(5));
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
            ->where('time_to_retry_dt', '<=', CarbonImmutable::now());
    }

    public function scopeOlderThanSeconds(Builder $query, int $seconds): void
    {
        $query->where('updated_at', '<', CarbonImmutable::now()->subSeconds($seconds));
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('is_complete', 1);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('is_buried', 1);
    }

    public function scopeRunning(Builder $query): void
    {
        $query->where('is_reserved', 1);
    }
    public function scopePending(Builder $query): void
    {
        $query->where('attempts', 0);
    }

    public function scopeQueuesWorkload(Builder $query): void
    {
        $query->pending()
            ->select('queue', $query->raw('count(*) as count'))
            ->groupBy('queue');
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
