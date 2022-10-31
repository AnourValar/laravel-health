<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class Www2NoneCheck extends Check
{
    /**
     * @var array
     */
    protected array $shouldBeRedirected = [];

    /**
     * @param array|string $urls
     * @return self
     */
    public function shouldBeRedirected(array|string $urls): self
    {
        $this->shouldBeRedirected = $this->normilizeUrls($urls);

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->shouldBeRedirected) {
            throw new \Exception('Urls are not set.');
        }

        $result = Result::make();
        $this->label('WWW -> None');


        try {
            $failed = [];

            foreach ($this->shouldBeRedirected as $url) {
                if (! $this->isRedirected($url)) {
                    $failed[] = sprintf('%s 301 redirect missing.', $url);
                }
            }
        } catch (ExternalException $e) {
            return $result->failed($e->getMessage());
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(sprintf('%d url(s) checked.', count($this->shouldBeRedirected)));
        return $result->ok();
    }

    /**
     * @param string $url
     * @throws \AnourValar\LaravelHealth\Exceptions\ExternalException
     * @return bool
     */
    protected function isRedirected(string $url): bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new ExternalException(sprintf('%s is not reachable.', $url));
        }

        return preg_match('#(^|\s)HTTP/[\d\.]+ 301 Moved Permanently($|\s)#i', $result)
            && preg_match('#(^|\s)Location: '.preg_quote(preg_replace('#^(https?://)(www\.|)#i', '$1', $url)).'/?($|\s)#i', $result);
    }

    /**
     * @param array|string $urls
     * @return array
     */
    private function normilizeUrls(array|string $urls): array
    {
        $urls = (array) $urls;

        foreach ($urls as &$url) {
            $url = url($url);
            $url = preg_replace('#^(https?://)(www\.|)#i', '$1www.', $url);
        }
        unset($url);

        return $urls;
    }
}
