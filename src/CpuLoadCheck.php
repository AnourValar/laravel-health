<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class CpuLoadCheck extends Check
{
    /**
     * @var float
     */
    protected float $lastMinute = 2.5;

    /**
     * @var float
     */
    protected float $last5Minutes = 2.0;

    /**
     * @var float
     */
    protected float $last15Minutes = 1.5;

    /**
     * @param float $lastMinute
     * @param float $last5Minutes
     * @param float $last15Minutes
     * @return self
     */
    public function failWhenLoadIsHigher(float $lastMinute, float $last5Minutes, float $last15Minutes): self
    {
        $this->lastMinute = $lastMinute;
        $this->last5Minutes = $last5Minutes;
        $this->last15Minutes = $last15Minutes;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $cpuLoad = sys_getloadavg();

        $result = Result::make();
        $result->shortSummary(implode(' ', $cpuLoad));

        if (
            $cpuLoad[0] > $this->lastMinute
            || $cpuLoad[1] > $this->last5Minutes
            || $cpuLoad[2] > $this->last15Minutes
        ) {
            return $result->failed();
        }

        return $result->ok();
    }
}
