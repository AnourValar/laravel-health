<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\RedisConfigCheck;

class RedisConfigCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_throws_when_connections_are_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Connections are not set.');

        (new RedisConfigCheck())->connections([])->run();
    }
}
