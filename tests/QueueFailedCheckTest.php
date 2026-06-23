<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\QueueFailedCheck;

class QueueFailedCheckTest extends AbstractSuite
{
    /**
     * With the "null" failed-jobs provider there are no failed jobs => ok.
     *
     * @return void
     */
    public function test_ok_when_no_failed_jobs(): void
    {
        config(['queue.failed' => ['driver' => null]]);
        $this->app->forgetInstance('queue.failer');

        $result = (new QueueFailedCheck())->run();

        $this->assertCheckOk($result);
    }
}
