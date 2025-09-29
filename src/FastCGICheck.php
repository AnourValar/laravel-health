<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Illuminate\Contracts\Encryption\DecryptException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class FastCGICheck extends Check
{
    /**
     * @var string
     */
    protected string $url;

    /**
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->url = url($url);
        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->url) {
            throw new \Exception('Url is not set.');
        }

        if (! $this->label) {
            $this->label('FastCGI');
        }
        $result = Result::make();

        try {
            $failed = [];
            $data = $this->secured($this->url);

            if (! $data['is_secure']) {
                $failed[] = 'Not secured (http scheme)';
            }

            if (parse_url($this->url, PHP_URL_HOST) != $data['host']) {
                $failed[] = 'Host mismatch';
            }
        } catch (ExternalException|DecryptException $e) {
            return $result->failed($e->getMessage());
        }

        if ($failed) {
            return $result->failed(
                sprintf('%s has issues: %s', parse_url($this->url, PHP_URL_HOST), implode(', ', $failed))
            );
        }

        return $result->ok();
    }

    /**
     * @param string $url
     * @throws \AnourValar\LaravelHealth\Exceptions\ExternalException
     * @return array
     */
    protected function secured(string $url): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        if (! $result) {
            throw new ExternalException(sprintf('%s is not reachable.', $url));
        }

        return decrypt($result);
    }
}
