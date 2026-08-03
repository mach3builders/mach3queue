<?php

use Mach3queue\Support\Connections;

describe('Connections', function () {

    test('returns the callback result when nothing fails', function () {
        expect(Connections::retrying(fn () => 'result'))->toBe('result');
    });

    test('refreshes and retries once when the callback loses the connection', function () {
        $refreshed = 0;
        $attempts = 0;

        Connections::refreshUsing(function () use (&$refreshed) {
            $refreshed++;
        });

        $result = Connections::retrying(function () use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                throw new PDOException(
                    'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'
                );
            }

            return 'reconnected';
        });

        expect($result)->toBe('reconnected')
            ->and($attempts)->toBe(2)
            ->and($refreshed)->toBe(1);
    });

    test('rethrows an exception that is not a lost connection without refreshing', function () {
        $refreshed = 0;
        $attempts = 0;

        Connections::refreshUsing(function () use (&$refreshed) {
            $refreshed++;
        });

        $run = function () use (&$attempts) {
            Connections::retrying(function () use (&$attempts) {
                $attempts++;

                throw new RuntimeException('something else broke');
            });
        };

        expect($run)->toThrow(RuntimeException::class);

        expect($attempts)->toBe(1)
            ->and($refreshed)->toBe(0);
    });

    test('rethrows when the retry loses the connection again', function () {
        $attempts = 0;

        Connections::refreshUsing(fn () => null);

        $run = function () use (&$attempts) {
            Connections::retrying(function () use (&$attempts) {
                $attempts++;

                throw new PDOException(
                    'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away'
                );
            });
        };

        expect($run)->toThrow(PDOException::class);

        expect($attempts)->toBe(2);
    });

    test('retries even when no refresh callback is registered', function () {
        $attempts = 0;

        $result = Connections::retrying(function () use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                throw new PDOException('MySQL server has gone away');
            }

            return 'ok';
        });

        expect($result)->toBe('ok')
            ->and($attempts)->toBe(2);
    });
});
