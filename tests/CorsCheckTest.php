<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\CorsCheck;

class CorsCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_throws_when_url_is_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Url is not set.');

        (new CorsCheck())->run();
    }

    /**
     * @return void
     */
    public function test_throws_when_hosts_are_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Hosts are not set.');

        (new CorsCheck())->url('https://localhost')->run();
    }

    /**
     * A host equal to the target host is always allowed (same-origin shortcut,
     * no outbound request is performed).
     *
     * @return void
     */
    public function test_ok_when_allowed_host_matches_target(): void
    {
        $result = (new CorsCheck())
            ->url('https://localhost')
            ->allowed('localhost')
            ->run();

        $this->assertCheckOk($result);
        $this->assertSame('1 host(s) checked.', $result->shortSummary);
    }

    /**
     * A disallowed host that resolves to the same origin must be reported.
     *
     * @return void
     */
    public function test_fails_when_disallowed_host_matches_target(): void
    {
        $result = (new CorsCheck())
            ->url('https://localhost')
            ->disallowed('localhost')
            ->run();

        $this->assertCheckFailed($result);
        $this->assertStringContainsString('CORS allowed.', $result->notificationMessage);
    }

    /**
     * The default label is applied when none was provided.
     *
     * @return void
     */
    public function test_default_label_is_applied(): void
    {
        $check = (new CorsCheck())->url('https://localhost')->allowed('localhost');
        $check->run();

        $this->assertSame('CORS', $check->getLabel());
    }
}
