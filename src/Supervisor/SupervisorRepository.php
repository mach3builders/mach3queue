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
            ->where('name', $name)
            ->first();
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
        $match = ['name' => 'supervisor:'.$supervisor->name];

        DB::table(self::TABLE)->updateOrInsert($data, $match);
    }
}