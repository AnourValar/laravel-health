<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class CacheHeadersCheck extends Check
{
    /**
     * @var array
     */
    protected array $shouldBeCached = [];

    /**
     * @var array
     */
    protected array $shouldNotBeCached = [];

    /**
     * @param array|string $urls
     * @return self
     */
    public function shouldBeCached(array|string $urls): self
    {
        $this->shouldBeCached = $this->normalizeUrls($urls);

        return $this;
    }

    /**
     * @param array|string $urls
     * @return self
     */
    public function shouldNotBeCached(array|string $urls): self
    {
        $this->shouldNotBeCached = $this->normalizeUrls($urls);

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->shouldBeCached && ! $this->shouldNotBeCached) {
            throw new \Exception('Urls are not set.');
        }

        $result = Result::make();


        try {
            $failed = [];

            foreach ($this->shouldBeCached as $url) {
                if (! $this->isCached($url)) {
                    $failed[] = sprintf('%s is not cached.', $url);
                }
            }

            foreach ($this->shouldNotBeCached as $url) {
                if ($this->isCached($url)) {
                    $failed[] = sprintf('%s is cached.', $url);
                }
            }
        } catch (ExternalException $e) {
            return $result->failed($e->getMessage());
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(
            sprintf('%d url(s) checked.', (count($this->shouldBeCached) + count($this->shouldNotBeCached)))
        );
        return $result->ok();
    }

    /**
     * @param string $url
     * @throws \AnourValar\LaravelHealth\Exceptions\ExternalException
     * @return bool
     */
    protected function isCached(string $url): bool
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

        preg_match('#(^|\s)Cache-Control:([^\n\r]+)#i', $result, $result);
        return preg_match('#[1-9]#', ($result[2] ?? ''));
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
