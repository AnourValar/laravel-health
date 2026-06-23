<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\SSLCertCheck;

class SSLCertCheckTest extends AbstractSuite
{
    /**
     * The url setter stores the value as-is, so an empty string triggers the guard.
     *
     * @return void
     */
    public function test_throws_when_url_is_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Url is not set.');

        (new SSLCertCheck())->url('')->run();
    }
}
