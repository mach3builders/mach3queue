<?php

namespace Mach3queue\Action;

use Carbon\CarbonImmutable;
use Mach3queue\Supervisor\SupervisorRepository;

class ExpireSupervisors
{
    public function __invoke(): void
    {
        foreach (SupervisorRepository::all() as $supervisor) {
            $expired = CarbonImmutable::now()
                ->subSeconds(14)
                ->isAfter($supervisor->updated_at);

            if (! $expired) {
                continue;
            }

            SupervisorRepository::forget($supervisor->name);
        }
    }
}