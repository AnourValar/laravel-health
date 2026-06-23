<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\DirectoryPermissionsCheck;

class DirectoryPermissionsCheckTest extends AbstractSuite
{
    /**
     * @var array<int, string>
     */
    private array $cleanup = [];

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->cleanup as $dir) {
            if (is_dir($dir)) {
                @chmod($dir, 0777);
                @rmdir($dir);
            }
        }
        $this->cleanup = [];

        parent::tearDown();
    }

    /**
     * @param int $mode
     * @return string
     */
    private function makeDir(int $mode = 0777): string
    {
        $dir = sys_get_temp_dir() . '/laravel_health_' . bin2hex(random_bytes(8));
        mkdir($dir, 0777);
        chmod($dir, $mode);
        $this->cleanup[] = $dir;

        return $dir;
    }

    /**
     * @return void
     */
    public function test_throws_when_nothing_is_set(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Paths are not set.');

        (new DirectoryPermissionsCheck())->run();
    }

    /**
     * @return void
     */
    public function test_throws_when_path_is_null(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Path cannot be null.');

        (new DirectoryPermissionsCheck())->writable([null])->run();
    }

    /**
     * @return void
     */
    public function test_ok_for_writable_directory(): void
    {
        $dir = $this->makeDir(0777);

        $result = (new DirectoryPermissionsCheck())->writable([$dir])->run();

        $this->assertCheckOk($result);
        $this->assertSame('1 dir(s) checked.', $result->shortSummary);
    }

    /**
     * @return void
     */
    public function test_fails_when_writable_directory_is_missing(): void
    {
        $missing = sys_get_temp_dir() . '/laravel_health_missing_' . bin2hex(random_bytes(8));

        $result = (new DirectoryPermissionsCheck())->writable([$missing])->run();

        $this->assertCheckFailed($result);
        $this->assertStringContainsString('does not exist.', $result->notificationMessage);
    }

    /**
     * @return void
     */
    public function test_fails_when_directory_should_not_be_writable_but_is(): void
    {
        $dir = $this->makeDir(0777);

        $result = (new DirectoryPermissionsCheck())->notWritable([$dir])->run();

        $this->assertCheckFailed($result);
        $this->assertStringContainsString('is writable.', $result->notificationMessage);
    }

    /**
     * @return void
     */
    public function test_ok_for_not_writable_directory(): void
    {
        $dir = $this->makeDir(0500);

        if (is_writable($dir)) {
            $this->markTestSkipped('Cannot create a non-writable directory (probably running as root).');
        }

        $result = (new DirectoryPermissionsCheck())->notWritable([$dir])->run();

        $this->assertCheckOk($result);
        $this->assertSame('1 dir(s) checked.', $result->shortSummary);
    }

    /**
     * @return void
     */
    public function test_fails_when_directory_should_be_writable_but_is_not(): void
    {
        $dir = $this->makeDir(0500);

        if (is_writable($dir)) {
            $this->markTestSkipped('Cannot create a non-writable directory (probably running as root).');
        }

        $result = (new DirectoryPermissionsCheck())->writable([$dir])->run();

        $this->assertCheckFailed($result);
        $this->assertStringContainsString('is not writable.', $result->notificationMessage);
    }

    /**
     * @return void
     */
    public function test_counts_both_writable_and_not_writable(): void
    {
        $writable = $this->makeDir(0777);
        $notWritable = $this->makeDir(0500);

        if (is_writable($notWritable)) {
            $this->markTestSkipped('Cannot create a non-writable directory (probably running as root).');
        }

        $result = (new DirectoryPermissionsCheck())
            ->writable([$writable])
            ->notWritable([$notWritable])
            ->run();

        $this->assertCheckOk($result);
        $this->assertSame('2 dir(s) checked.', $result->shortSummary);
    }
}
