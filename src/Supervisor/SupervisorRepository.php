<?php

namespace Mach3queue\Supervisor;

use Carbon\CarbonImmutable;
use Illuminate\Database\Capsule\Manager as DB;

class SupervisorRepository
{
    const TABLE = 'supervisors';

    public static function get(string $name): ?object
    {
        return DB::table(self::TABLE)
            ->where('name', 'supervisor:'.$name)
            ->orWhere('name', $name)
            ->first();
    }

    public static function forget(string $name): void
    {
        DB::table(self::TABLE)
            ->where('name', 'supervisor:'.$name)
            ->orWhere('name', $name)
            ->delete();
    }

    public static function updateOrCreate(Supervisor $supervisor): void
    {
        $match = [
            'name' => 'supervisor:'.$supervisor->name
        ];
        $data = [
            'master' => implode(':', explode(':', $supervisor->name, -1)),
            'pid' => $supervisor->pid(),
            'status' => $supervisor->working ? 'running' : 'paused',
            'processes' => count($supervisor->process_pool),
            'options' => $supervisor->options->toJson(),
            'updated_at' => CarbonImmutable::now(),
        ];

        DB::table(self::TABLE)->updateOrInsert($match, $data);
    }

    public static function updateOrCreateMaster(MasterSupervisor $master): void
    {
        $match = [
            'name' => $master->name,
            'master' => null
        ];
        $data = [
            'pid' => $master->pid(),
            'status' => $master->working ? 'running' : 'paused',
            'processes' => count($master->supervisors),
            'updated_at' => CarbonImmutable::now(),
        ];

        DB::table(self::TABLE)->updateOrInsert($match, $data);
    }

    public static function allMasters(): array
    {
        return DB::table(self::TABLE)
            ->where('master', null)
            ->get()
            ->toArray();
    }

    public static function all(): array
    {
        return DB::table(self::TABLE)
            ->get()
            ->toArray();
    }
}