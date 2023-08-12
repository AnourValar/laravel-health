<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class RootCheck extends Check
{
    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        if (posix_getuid() === 0) {
            return $result->failed('Run as root.');
        }

        return $result->ok('Run as none root.');
    }
}
