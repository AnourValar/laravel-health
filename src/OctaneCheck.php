<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class OctaneCheck extends Check // use a ping checker in case of docker
{
    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     * @psalm-suppress UndefinedClass
     */
    public function run(): Result
    {
        $result = Result::make();

        $handler = match (config('octane.server')) {
            'swoole' => \Laravel\Octane\Swoole\ServerProcessInspector::class,
            'roadrunner' => \Laravel\Octane\RoadRunner\ServerProcessInspector::class,
            'frankenphp' => \Laravel\Octane\FrankenPhp\ServerProcessInspector::class,
            default => null,
        };

        if (! $handler) {
            return $result->failed('Octane driver not supported.');
        }

        if (! \App::make($handler)->serverIsRunning()) {
            return $result->failed('Octane server is not running.');
        }

        return $result->ok();
    }
}
