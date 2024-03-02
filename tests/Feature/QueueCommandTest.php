<?php

use Mach3queue\Console\QueueCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Mach3queue\Supervisor\SupervisorRepository;
use Illuminate\Database\Capsule\Manager as Database;
use Symfony\Component\Console\Output\ConsoleOutput;

describe('Queue command', function () {

    test('can create queue', function () {
        // setup
        $input = new ArgvInput([]);
        $output = new ConsoleOutput;
        $command = new QueueCommand([
            'database' => [
                'driver' => 'sqlite',
                'host' => 'localhost',
                'database' => ':memory:',
                'username' => 'test',
                'password' => 'test',
            ],
            'supervisors' => [...defaultSupervisorConfig1()]
        ], false);

        // run
        $command->run($input, $output);

        // assert
        expect(SupervisorRepository::allMasters())->not->toBeEmpty();
    });
});