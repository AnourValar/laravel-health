<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\PusherCheck;

class PusherCheckTest extends AbstractSuite
{
    /**
     * A non-pusher driver is unsupported and raises a runtime exception.
     *
     * @return void
     */
    public function test_throws_for_unsupported_driver(): void
    {
        config([
            'broadcasting.default' => 'health_test',
            'broadcasting.connections.health_test' => ['driver' => 'log'],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported driver for connection: health_test');

        (new PusherCheck())->run();
    }

    /**
     * The explicit connection name is honoured over the default one.
     *
     * @return void
     */
    public function test_throws_for_unsupported_driver_with_explicit_connection(): void
    {
        config([
            'broadcasting.connections.health_explicit' => ['driver' => 'null'],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported driver for connection: health_explicit');

        (new PusherCheck())->connection('health_explicit')->run();
    }
}
