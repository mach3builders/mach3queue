<?php

namespace Mach3queue\Supervisor;

use Illuminate\Database\Capsule\Manager as DB;
use Mach3queue\SuperVisor\Supervisor;

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
        $data = [
            'master' => implode(':', explode(':', $supervisor->name, -1)),
            'pid' => $supervisor->pid(),
            'status' => $supervisor->working ? 'running' : 'paused',
            'processes' => count($supervisor->process_pool),
            'options' => $supervisor->options->toJson(),
        ];
        $match = [
            'name' => 'supervisor:'.$supervisor->name
        ];

        DB::table(self::TABLE)->updateOrInsert($data, $match);
    }

    public static function updateOrCreateMaster(MasterSupervisor $master): void
    {
        $data = [
            'pid' => $master->pid(),
            'status' => $master->working ? 'running' : 'paused',
            'processes' => count($master->supervisors),
        ];
        $match = [
            'name' => $master->name,
            'master' => null
        ];

        DB::table(self::TABLE)->updateOrInsert($data, $match);
    }

    public static function allMasters(): array
    {
        return DB::table(self::TABLE)
            ->where('master', null)
            ->get()
            ->toArray();
    }
}