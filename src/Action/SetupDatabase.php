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
        $this->prepare($database);
    }

    private function prepare(Database $database): void
    {
        if ($database->schema()->hasTable('jobs')) {
            return;
        }

        $database->schema()->create('jobs', function ($table) {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->dateTime('added_dt');
            $table->dateTime('send_dt');
            $table->integer('priority')->default(0);
            $table->boolean('is_reserved')->default(0);
            $table->dateTime('reserved_dt')->nullable();
            $table->boolean('is_buried')->default(0);
            $table->dateTime('buried_dt')->nullable();
            $table->boolean('is_complete')->default(0);
            $table->dateTime('complete_dt')->nullable();
            $table->integer('attempts')->default(0);
            $table->dateTime('time_to_retry_dt')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            $table->index(['queue', 'send_dt', 'is_buried', 'is_reserved']);
        });
    }
}