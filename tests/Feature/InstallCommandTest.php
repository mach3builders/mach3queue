<?php


use Mach3queue\Console\InstallCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Database\Capsule\Manager as Database;

describe('Install command', function () {

    test('can install database', function () {
        // setup
        $command = new InstallCommand;

        // run
        $command->run(new ArgvInput([]), new ConsoleOutput);

        expect(Database::schema()->hasTable('jobs'))->toBeTrue()
            ->and(Database::schema()->hasTable('supervisors'))->toBeTrue();
    });

});