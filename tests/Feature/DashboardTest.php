<?php

use Mach3queue\Dashboard\Dashboard;
use Mach3queue\Supervisor\MasterSupervisor;

describe('Dashboard', function () {

    test('can get dashboard data', function () {
        // setup
        $master = new MasterSupervisor([
            ...trimOptions(),
            'supervisors' => [...defaultSupervisorConfig1()]
        ]);
        addFakeJobToQueue(5);

        // run
        $master->loop();

        // assert
        $data = json_decode(Dashboard::parse(['data' => 'dashboard']));

        expect($data->active)->toBeTrue()
            ->and($data->supervisors)->toHaveCount(0)
            ->and($data->completedJobs)->toBe(0)
            ->and($data->failedJobs)->toBe(0)
            ->and($data->pendingJobs)->toBe(5);
    });

});