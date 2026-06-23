<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\QueueSizeCheck;

class QueueSizeCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_throws_when_queues_are_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Queues are not set.');

        (new QueueSizeCheck())->run();
    }
}
