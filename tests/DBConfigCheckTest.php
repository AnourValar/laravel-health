<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\DBConfigCheck;

class DBConfigCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_throws_when_params_are_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Params are not set.');

        (new DBConfigCheck())->run();
    }
}
