<?php

namespace AnourValar\LaravelHealth\Tests;

use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;

abstract class AbstractSuite extends \Orchestra\Testbench\TestCase
{
    /**
     * Init
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [

        ];
    }

    protected function getPackageAliases($app)
    {
        return [

        ];
    }

    /**
     * Assert that the check result is "ok".
     *
     * @param \Spatie\Health\Checks\Result $result
     * @param string|null $message
     * @return void
     */
    protected function assertCheckOk(Result $result, ?string $message = null): void
    {
        $this->assertTrue(
            $result->status->equals(Status::ok()),
            sprintf('Expected "ok", got "%s" (%s).', (string) $result->status, $result->notificationMessage)
        );

        if ($message !== null) {
            $this->assertSame($message, $result->notificationMessage);
        }
    }

    /**
     * Assert that the check result is "warning".
     *
     * @param \Spatie\Health\Checks\Result $result
     * @param string|null $message
     * @return void
     */
    protected function assertCheckWarning(Result $result, ?string $message = null): void
    {
        $this->assertTrue(
            $result->status->equals(Status::warning()),
            sprintf('Expected "warning", got "%s" (%s).', (string) $result->status, $result->notificationMessage)
        );

        if ($message !== null) {
            $this->assertSame($message, $result->notificationMessage);
        }
    }

    /**
     * Assert that the check result is "failed".
     *
     * @param \Spatie\Health\Checks\Result $result
     * @param string|null $message
     * @return void
     */
    protected function assertCheckFailed(Result $result, ?string $message = null): void
    {
        $this->assertTrue(
            $result->status->equals(Status::failed()),
            sprintf('Expected "failed", got "%s" (%s).', (string) $result->status, $result->notificationMessage)
        );

        if ($message !== null) {
            $this->assertSame($message, $result->notificationMessage);
        }
    }
}
