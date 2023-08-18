<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Illuminate\Contracts\Encryption\DecryptException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class FastCGICheck extends Check
{
    /**
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * @param string|null $url
     * @return self
     */
    public function url(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        $url = $this->url ?? config('app.url');

        $this->label('FastCGI');
        $result = Result::make();
        $failed = [];

        if (! \Request::isSecure()) {
            $failed[] = 'Not secured (http scheme)';
        }

        if (url('') != $url) {
            $failed[] = sprintf('Url mismatch: %s != %s', url(''),  $url);
        }

        if ($failed) {
            return $result->failed(sprintf('Issues: %s', implode(', ', $failed)));
        }

        return $result->ok();
    }
}
