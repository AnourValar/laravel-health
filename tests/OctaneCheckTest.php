<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\OctaneCheck;

class OctaneCheckTest extends AbstractSuite
{
    /**
     * Without a configured Octane server the driver is unsupported.
     *
     * @return void
     */
    public function test_fails_when_octane_driver_is_not_supported(): void
    {
        config(['octane.server' => null]);

        $result = (new OctaneCheck())->run();

        $this->assertCheckFailed($result, 'Octane driver not supported.');
    }
}
