<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class HstsCheck extends Check
{
    /**
     * @var array
     */
    protected array $urls = [];

    /**
     * @param array|string $urls
     * @return self
     */
    public function urls(array|string $urls): self
    {
        $urls = (array) $urls;
        foreach ($urls as &$url) {
            $url = url($url);
        }
        unset($url);

        $this->urls = $urls;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->urls) {
            throw new \Exception('Urls are not set.');
        }

        $result = Result::make();
        $this->label('HSTS');


        try {
            $failed = [];

            foreach ($this->urls as $url) {
                if (! $this->isSet($url)) {
                    $failed[] = sprintf('%s - is not set.', $url);
                }
            }
        } catch (ExternalException $e) {
            return $result->failed($e->getMessage());
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(sprintf('%d url(s) checked.', count($this->urls)));
        return $result->ok();
    }

    /**
     * @param string $url
     * @throws \AnourValar\LaravelHealth\Exceptions\ExternalException
     * @return bool
     */
    protected function isSet(string $url): bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new ExternalException(sprintf('%s is not reachable.', $url));
        }

        return preg_match('#[\r\n]Strict-Transport-Security\:.+#i', $result);
    }
}
