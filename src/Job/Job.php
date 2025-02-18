<?php

namespace Mach3queue\Job;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use function Opis\Closure\{serialize};

/**
 * @method static Job nextJobForPipeLines(array|string[] $getPipelines, int $maxRetries = 3, int $timeToRetry = 60)
 * @method static Job where(string $string, int $id)
 * @method static Job olderThanSeconds(int $completed_seconds)
 * @method static Job completed()
 * @method static Job failed()
 * @method static Job running()
 * @method static Job pending()
 * @method static Job queuesWorkload()
 * @method lockForUpdate()
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property string $callback
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
    public static string $timeout_message = 'Job has timed out';
    public static string $memory_exceeded_message = 'Job memory limit exceeded';

    public function scopeNextJobForPipelines(
        Builder $query,
        array $pipelines,
        int $maxRetries = 3,
    ): void {
        $query->whereIn('queue', $pipelines)
            ->sendBeforeNow()
            ->isNotComplete()
            ->isNotReservedOrReservedTimeExpired()
            ->isNotAttemptedOrTimeToRetryIsNow($maxRetries)
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

    public function scopeIsNotReservedOrReservedTimeExpired(
        Builder $query,
        int $timeToRetry = 60
    ): void {
        $query->where(function ($query) use ($timeToRetry) {
            $query->isNotReserved()->orWhere(function ($query) use ($timeToRetry) {
                $query->reservedIsExpired($timeToRetry);
            });
        });
    }

    public function scopeIsNotReserved(Builder $query): void
    {
        $query->where('is_reserved', 0);
    }

    public function scopeReservedIsExpired(Builder $query): void
    {
        $time = CarbonImmutable::now()->subMinutes(5);

        $query->where('is_reserved', 1)->where('reserved_dt', '<=', $time);
    }

    public function scopeIsNotAttemptedOrTimeToRetryIsNow(
        Builder $query,
        int $maxRetries = 3
    ): void {
        $query->where(function ($query) use ($maxRetries) {
            $query->isNotAttempted()
                ->orWhere(function ($query) use ($maxRetries) {
                    $query->timeToRetryIsNow($maxRetries);
                });
        });
    }

    public function scopeIsNotAttempted(Builder $query): void
    {
        $query->where('attempts', 0);
    }

    public function scopeTimeToRetryIsNow(Builder $query, int $maxRetries = 3): void
    {
        $query->where('attempts', '>', 0)
            ->where('attempts', '<', $maxRetries)
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

        return Status::UNKNOWN;
    }

    public function after(callable $callback): static
    {
        $this->callback = serialize($callback);
        $this->save();

        return $this;
    }
}
