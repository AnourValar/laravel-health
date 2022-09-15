<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class XdebugCheck extends Check
{
    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        if (in_array('xdebug', array_map('mb_strtolower', get_loaded_extensions()))) {
            return $result->failed('XDebug extension is installed.');
        }

        $result->shortSummary('XDebug extension is missing.');
        return $result->ok();
    }
}
