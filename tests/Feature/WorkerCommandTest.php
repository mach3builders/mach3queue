<?php

use Mach3queue\Console\WorkerCommand;
use Mach3queue\Support\Connections;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

describe('Worker command', function () {

    test('registers the before_job callback as the connection refresher', function () {
        $refreshed = 0;

        $command = new WorkerCommand([
            'before_job' => function () use (&$refreshed) {
                $refreshed++;
            },
        ]);

        $command->run(
            new ArgvInput(['queue', '--stop-when-empty', '--queue=default']),
            new NullOutput
        );

        Connections::refresh();

        expect($refreshed)->toBe(1);
    });

    test('leaves the refresher unset when no before_job is configured', function () {
        $command = new WorkerCommand([]);

        $command->run(
            new ArgvInput(['queue', '--stop-when-empty', '--queue=default']),
            new NullOutput
        );

        expect(fn () => Connections::refresh())->not->toThrow(Throwable::class);
    });
});
