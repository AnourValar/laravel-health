<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\FilesystemCheck;

class FilesystemCheckTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_throws_when_disks_are_not_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Disks is not set.');

        (new FilesystemCheck())->run();
    }

    /**
     * An unknown disk cannot be reached and must be reported as failed.
     *
     * @return void
     */
    public function test_fails_for_unreachable_disk(): void
    {
        $result = (new FilesystemCheck())->disks(['this_disk_does_not_exist'])->run();

        $this->assertCheckFailed($result, 'Disk "this_disk_does_not_exist" is not reachable.');
    }
}
