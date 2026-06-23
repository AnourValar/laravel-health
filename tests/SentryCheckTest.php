<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\SentryCheck;

class SentryCheckTest extends AbstractSuite
{
    /**
     * With no DSN configured (neither explicit nor via sentry.dsn) the check fails early.
     *
     * @return void
     */
    public function test_fails_when_dsn_is_not_set(): void
    {
        config(['sentry.dsn' => null]);

        $result = (new SentryCheck())->run();

        $this->assertCheckFailed($result, 'DSN is not set.');
    }
}
