<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class GzipCheck extends Check
{
    /**
     * @var array
     */
    protected array $shouldBeGzipped = [];

    /**
     * @var array
     */
    protected array $shouldNotBeGzipped = [];

    /**
     * @param array|string $urls
     * @return self
     */
    public function shouldBeGzipped(array|string $urls): self
    {
        $this->shouldBeGzipped = $this->normalizeUrls($urls);

        return $this;
    }

    /**
     * @param array|string $urls
     * @return self
     */
    public function shouldNotBeGzipped(array|string $urls): self
    {
        $this->shouldNotBeGzipped = $this->normalizeUrls($urls);

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->shouldBeGzipped && ! $this->shouldNotBeGzipped) {
            throw new \Exception('Urls are not set.');
        }

        $result = Result::make();


        try {
            $failed = [];

            foreach ($this->shouldBeGzipped as $url) {
                if (! $this->isGzipped($url)) {
                    $failed[] = sprintf('%s is not gzipped.', $url);
                }
            }

            foreach ($this->shouldNotBeGzipped as $url) {
                if ($this->isGzipped($url)) {
                    $failed[] = sprintf('%s is gzipped.', $url);
                }
            }
        } catch (ExternalException $e) {
            return $result->failed($e->getMessage());
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(
            sprintf('%d url(s) checked.', (count($this->shouldBeGzipped) + count($this->shouldNotBeGzipped)))
        );
        return $result->ok();
    }

    /**
     * @param string $url
     * @throws \AnourValar\LaravelHealth\Exceptions\ExternalException
     * @return bool
     */
    protected function isGzipped(string $url): bool
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Encoding: gzip']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new ExternalException(sprintf('%s is not reachable.', $url));
        }

        return preg_match('#(^|\s)Content-Encoding: gzip($|\s)#i', $result);
    }

    /**
     * @param array|string $urls
     * @return array
     */
    private function normalizeUrls(array|string $urls): array
    {
        $urls = (array) $urls;

        foreach ($urls as &$url) {
            $url = url($url);
        }
        unset($url);

        return $urls;
    }
}
