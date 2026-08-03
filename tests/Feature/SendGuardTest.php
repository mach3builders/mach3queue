<?php

use Mach3queue\Queue\QueueManager as Queue;

describe('send guard', function () {

    test('job is not reservable until the send guard has elapsed', function () {
        addFakeJobToQueue();

        // The one second guard in Job::scopeSendBeforeNow exists so that a
        // caller doing Queue::addJob(...)->after(...) can attach its callback
        // before a worker is able to reserve the job. See bceb373.
        expect(Queue::getNextJob())->toBeNull();

        advancePastSendGuard();

        expect(Queue::getNextJob())->not->toBeNull();
    });
});
