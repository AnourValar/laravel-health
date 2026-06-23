<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\Http2HttpsCheck;

class Http2HttpsCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_throws_when_urls_are_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Urls are not set.');

        (new Http2HttpsCheck())->run();
    }
}
