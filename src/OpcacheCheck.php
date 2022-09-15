<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class OpcacheCheck extends Check
{
    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        if (empty(opcache_get_status()['opcache_enabled'])) {
            return $result->failed('Opcache is disabled.');
        }

        if (empty(opcache_get_status()['jit']['enabled'])) {
            return $result->warning('JIT is disabled.');
        }

        return $result->ok();
    }
}
