<?php

namespace AnourValar\LaravelHealth\Tests;

use AnourValar\LaravelHealth\CpuLoadCheck;

class CpuLoadCheckTest extends AbstractSuite
{
    /**
     * Thresholds far above any realistic load => ok.
     *
     * @return void
     */
    public function test_passes_when_load_is_below_thresholds(): void
    {
        $result = (new CpuLoadCheck())
            ->failWhenLoadIsHigher(PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX)
            ->run();

        $this->assertCheckOk($result);
        $this->assertNotSame('', $result->shortSummary);
    }

    /**
     * Negative thresholds are always exceeded => failed.
     *
     * @return void
     */
    public function test_fails_when_load_is_above_thresholds(): void
    {
        $result = (new CpuLoadCheck())
            ->failWhenLoadIsHigher(-1.0, -1.0, -1.0)
            ->run();

        $this->assertCheckFailed($result);
    }

    /**
     * The short summary holds the current load average values.
     *
     * @return void
     */
    public function test_short_summary_contains_load_average(): void
    {
        $result = (new CpuLoadCheck())
            ->failWhenLoadIsHigher(PHP_INT_MAX, PHP_INT_MAX, PHP_INT_MAX)
            ->run();

        $this->assertMatchesRegularExpression('#^[\d.]+ [\d.]+ [\d.]+$#', $result->shortSummary);
    }
}
