<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class SSLCertCheck extends Check
{
    /**
     * @var string
     */
    protected string $url;

    /**
     * @var int
     */
    protected int $warnExpiringDay = 10;

    /**
     * @var int
     */
    protected int $failExpiringDay = 2;

    /**
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param int $warnExpiringDay
     * @return self
     */
    public function warnWhenExpiringDay(int $warnExpiringDay): self
    {
        $this->warnExpiringDay = $warnExpiringDay;
        return $this;
    }

    /**
     * @param int $failExpiringDay
     * @return self
     */
    public function failWhenExpiringDay(int $failExpiringDay): self
    {
        $this->failExpiringDay = $failExpiringDay;
        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @throws \Exception
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->url) {
            throw new \Exception('Url is not set.');
        }

        $result = Result::make();
        if (! $this->label) {
            $this->label('SSL');
        }

        try {
            $expires = $this->getCert($this->url);
            if ($expires) {
                $expires = floor($expires / 60 / 60 / 24);
            }
        } catch (\ErrorException $e) {
            return $result->failed($e->getMessage());
        }

        if ($expires === false) {
            return $result->failed("Certificate for {$this->url} expired.");
        }

        if ($expires <= $this->failExpiringDay) {
            return $result->failed("Certificate for {$this->url} expires in $expires day(s).");
        }

        if ($expires <= $this->warnExpiringDay) {
            return $result->warning("Certificate for {$this->url} expires in $expires day(s).");
        }

        $result->shortSummary("Certificate for {$this->url} expires in $expires day(s).");
        return $result->ok();
    }

    /**
     * @param string $url
     * @return bool|int
     */
    protected function getCert(string $url): bool|int
    {
        $host = parse_url($url, PHP_URL_HOST) ?? $url;
        $host = mb_strtolower(preg_replace('|^www\.|i', '', $host));
        $port = parse_url($url, PHP_URL_PORT) ?? '443';

        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);
        $read = stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        $cert = stream_context_get_params($read);
        $cert = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        $now = now()->utc()->timestamp;

        if ($now < $cert['validFrom_time_t']) {
            return false;
        }

        if ($now > $cert['validTo_time_t']) {
            return false;
        }

        $domains = [str_replace('/CN=', '', $cert['name']), ...explode(', ', $cert['extensions']['subjectAltName'])];
        foreach ($domains as $altName) {
            $altName = preg_replace('|^\s*DNS\:|', '', $altName);
            $altName = mb_strtolower(trim($altName));

            if (
                $altName == $host
                || (strpos($altName, '*.') === 0 && str_ends_with($host, mb_substr($altName, 1)))
                || (strpos($altName, '*.') === 0 && mb_substr($altName, 2) === $host)
            ) {
                return $cert['validTo_time_t'] - $now;
            }
        }

        return false;
    }
}
