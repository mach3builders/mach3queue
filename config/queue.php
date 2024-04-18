<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bootstrap path
    |--------------------------------------------------------------------------
    |
    | This is the path to the bootstrap file, change this to your own
    | applications bootstrap file, that way, all the jobs will be
    | executed in the same environment as your application.
    | This has to be an absolute path.
    |
    */

    'bootstrap' => __DIR__.'/vendor/bootstrap.php',

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) it takes before
    | a job will be removed from the database, as a default, the
    | completed jobs will be removed after 60 minutes and the
    | failed jobs after 10080 minutes (1 week).
    |
    */

    'trim' => [
        'completed' => 60,
        'failed' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | This is where you configure the workers, each param is explained
    | with it,each supervisor can have multiple workers and will
    | handle everything, the workers will be autoscaled up or
    | down, but the supervisors will always remain the same.
    |
    */

    'supervisors' => [
        'supervisor-default' => [       // the key is the name of the supervisor
            'queue' => ['default'],     // the queues to work on, there can be multiple.
            'max_processes' => 10,      // the maximum number of worker processes.
            'min_processes' => 2,       // the minimum number of worker processes
            'timeout' => 60,            // the time in seconds before a worker will terminate itself.
            'memory' => 128,            // the memory limit in megabytes for the worker before it will terminate itself.
            'balance_cooldown' => 5,    // the time in seconds before the supervisor will check the workload again.
            'max_workload' => 5,        // the maximum workload before the supervisor will scale up.
        ],
    ],
];
