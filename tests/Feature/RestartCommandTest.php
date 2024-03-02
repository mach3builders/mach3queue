<?php

use Mach3queue\Supervisor\MasterSupervisor;
use Mach3queue\Supervisor\SupervisorOptions;

describe('Restart command', function () {

    test('can restart all supervisors', function () {
        // setup
        $master = new MasterSupervisor([]);
        $process = fakeProcess()->shouldIgnoreMissing();

        // test
        $process->expects()->signal(SIGUSR1)->once();

        // run
        $master->addSupervisorProcess(new SupervisorOptions, $process);
        $master->restart();
    });

});