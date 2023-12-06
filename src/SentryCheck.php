<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class SentryCheck extends Check
{
    /**
     * @var string|null
     */
    protected ?string $dsn = null;

    /**
     * @param string|null $dsn
     * @return self
     */
    public function dsn(?string $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     * @psalm-suppress UndefinedClass
     * @psalm-suppress UnusedVariable
     */
    public function run(): Result
    {
        $result = Result::make();

        // Get DSN
        $dsn = $this->dsn ?? config('sentry.dsn');
        if (! $dsn) {
            return $result->failed('DSN is not set.');
        }

        // Prepare the client
        $laravelClient = app(\Sentry\State\HubInterface::class)->getClient();
        $clientBuilder = \Sentry\ClientBuilder::create([
            'dsn' => $dsn,
            'release' => $laravelClient === null ? null : $laravelClient->getOptions()->getRelease(),
            'environment' => $laravelClient === null ? null : $laravelClient->getOptions()->getEnvironment(),
            'traces_sample_rate' => 1.0,
        ]);
        $clientBuilder->setSdkIdentifier(\Sentry\Laravel\Version::SDK_IDENTIFIER);
        $clientBuilder->setSdkVersion(\Sentry\Laravel\Version::SDK_VERSION);

        // Send the event
        $hub = new \Sentry\State\Hub($clientBuilder->getClient());
        $eventId = $hub->captureException(new \Exception('This is a test exception sent from the Laravel Health.'));

        // Result
        if (! $eventId) {
            return $result->failed('Cannot send an event.');
        }

        return $result->ok();
    }
}
