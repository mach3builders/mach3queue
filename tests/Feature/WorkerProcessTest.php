<?php

namespace Tests\Feature;

use Mach3queue\Process\WorkerProcess;

describe('Worker Process', function () {

    test('can read last output time', function () {
        // setup
        $worker_process = new WorkerProcess(createWorkProcess());
        $worker_process->start(fn() => null);

        // assert
        expect($worker_process->getIdleTime())->toBeLessThan(1);
        sleep(1);
        expect($worker_process->getIdleTime())->toBeGreaterThanOrEqual(1);
    });
});