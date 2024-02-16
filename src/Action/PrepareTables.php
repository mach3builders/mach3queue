<?php

namespace Mach3queue\Action;

use Illuminate\Database\Capsule\Manager as Database;

class PrepareTables
{
    public function execute(): void
    {
        $this->createJobsTable();
        $this->prepareSupervisorTable();
    }

    private function createJobsTable(): void
    {
        if (Database::schema()->hasTable('jobs')) {
            return;
        }

        Database::schema()->create('jobs', function ($table) {
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

    private function prepareSupervisorTable(): void
    {
        if (Database::schema()->hasTable('supervisors')) {
            return;
        }

        Database::schema()->create('supervisors', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('pid');
            $table->string('master')->nullable();
            $table->string('status')->nullable();
            $table->string('supervisors')->nullable();
            $table->string('processes')->nullable();
            $table->string('options')->nullable();
            $table->timestamps();
        });
    }
}