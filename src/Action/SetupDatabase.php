<?php

namespace Mach3queue\Action;

use Illuminate\Database\Capsule\Manager as Database;

class SetupDatabase
{
    public function execute($config): void
    {
        $database = new Database;
        $database->addConnection([
            'driver' => $config['driver'],
            'host' => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
        ]);
        $database->setAsGlobal();
        $database->bootEloquent();
    }
}