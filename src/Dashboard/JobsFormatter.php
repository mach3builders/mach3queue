<?php

namespace Mach3queue\Dashboard;

use Mach3queue\Job\Job;
use Illuminate\Support\Collection;

class JobsFormatter
{
    public static function format(Collection $jobs): string
    {
        $jobs->map(fn ($job) => self::formatJob($job));

        return json_encode($jobs);
    }

    private static function formatJob(Job $job): Job
    {
        $payload = unserialize($job->payload);
        $job->name = get_class($payload);
        $job->tags = self::getTags($payload);

        return $job;
    }

    private static function getTags(mixed $payload): array
    {
        $tags = [];

        foreach (get_object_vars($payload) as $key => $value) {
            $tags[] = [
                'name' => $key,
                'value' => self::getValue($value),
            ];
        }

        return $tags;
    }

    private static function getValue(mixed $value): mixed
    {
        if (is_object($value)) {
            return $value->id ?: null;
        }

        return $value;
    }
}