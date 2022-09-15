<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class QueueCheck extends Check
{
    /**
     * @var string
     */
    protected string $cacheKey = 'anourvalar:laravel-health:checks:queue:latestHeartbeatAt';

    /**
     * @var integer
     */
    protected int $maxAgeInSeconds = 70;

    /**
     * @param int $maxAgeInSeconds
     * @return self
     */
    public function maxAgeInSeconds(int $maxAgeInSeconds): self
    {
        $this->maxAgeInSeconds = $maxAgeInSeconds;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        $cache = \Cache::get($this->getCacheKey());

        if (! $cache) {
            return $result->failed('The job did not executed yet.');
        }

        $secondsAgo = now()->timestamp - $cache['run'];
        if ($secondsAgo > $this->maxAgeInSeconds) {
            return $result->failed("The last execution of the job was more than $secondsAgo seconds ago.");
        }


        $result->shortSummary("The job was in the queue for {$cache['delay']} seconds.");
        return $result->ok();
    }
}
