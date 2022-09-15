<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class QueueFailedCheck extends Check
{
    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @throws \Exception
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $result = Result::make();

        if ($count = count(\App::make('queue.failer')->all())) {
            return $result->failed("$count jobs failed.");
        }

        return $result->ok();
    }
}
