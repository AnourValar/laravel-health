<?php

namespace AnourValar\LaravelHealth;

use AnourValar\LaravelHealth\Exceptions\ExternalException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class CorsCheck extends Check
{
    /**
     * @var string
     */
    protected string $url = '';

    /**
     * @var array
     */
    protected array $allowed = [];

    /**
     * @var array
     */
    protected array $disallowed = [];

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
     * @param array|string $hosts
     * @return self
     */
    public function allowed(array|string $hosts): self
    {
        $this->allowed = $this->normalizeHosts($hosts);

        return $this;
    }

    /**
     * @param array|string $hosts
     * @return self
     */
    public function disallowed(array|string $hosts): self
    {
        $this->disallowed = $this->normalizeHosts($hosts);

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

        if (! $this->allowed && ! $this->disallowed) {
            throw new \Exception('Hosts are not set.');
        }

        $result = Result::make();
        $this->label('CORS');


        try {
            $failed = [];

            foreach ($this->allowed as $host) {
                if (! $this->isAllowed($host)) {
                    $failed[] = sprintf('%s - CORS disallowed.', $host);
                }
            }

            foreach ($this->disallowed as $host) {
                if ($this->isAllowed($host)) {
                    $failed[] = sprintf('%s - CORS allowed.', $host);
                }
            }
        } catch (ExternalException $e) {
            return $result->failed($e->getMessage());
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(sprintf('%d host(s) checked.', (count($this->allowed) + count($this->disallowed))));
        return $result->ok();
    }

    /**
     * @param string $host
     * @throws \AnourValar\LaravelHealth\Exceptions\ExternalException
     * @return bool
     */
    protected function isAllowed(string $host): bool
    {
        $urlParsed = parse_url($this->url);
        $urlParsed['host'] = mb_strtolower($urlParsed['host']);

        $hostParsed = parse_url($host);
        $hostParsed['host'] = mb_strtolower($hostParsed['host']);

        if ($urlParsed['host'] == $hostParsed['host'] && ($urlParsed['port'] ?? null) == ($hostParsed['port'] ?? null)) {
            return true;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Origin: $host"]);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new ExternalException(sprintf('%s is not reachable.', $this->url));
        }

        preg_match('#[\r\n]Access-Control-Allow-Origin:(.+)#i', $result, $values);
        if (! isset($values[1])) {
            return false;
        }

        $values = str_replace('*', '.*', trim($values[1]));
        return preg_match("#^$values$#i", $host);
    }

    /**
     * @param array|string $hosts
     * @return array
     */
    private function normalizeHosts(array|string $hosts): array
    {
        $hosts = (array) $hosts;

        foreach ($hosts as &$host) {
            if (! preg_match('#^https?\:\/\/#', $host) && ! preg_match('#^\/\/#', $host)) {
                $host = "https://$host";
            }
        }
        unset($host);

        return $hosts;
    }
}
