<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class RootCheck extends Check
{
    /**
     * @var bool
     */
    protected bool $shouldBeRoot = false;

    /**
     * @param bool $value
     * @return self
     */
    public function shouldBeRoot(bool $value): self
    {
        $this->shouldBeRoot = $value;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();
        $isRoot = posix_getuid() === 0;

        if (! $this->shouldBeRoot) {
            if ($isRoot) {
                return $result->failed('Run as root.');
            }

            return $result->ok('Run as none root.');
        } else {
            if (! $isRoot) {
                return $result->failed('Run as none root.');
            }

            return $result->ok('Run as root.');
        }
    }
}
