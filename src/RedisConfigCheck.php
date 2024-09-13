<?php

namespace AnourValar\LaravelHealth;

use Illuminate\Support\Facades\Redis;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class RedisConfigCheck extends Check
{
    /**
     * @var array
     */
    protected array $connections = ['default', 'cache'];

    /**
     * @param array $connections
     * @return self
     */
    public function connections(array $connections): self
    {
        $this->connections = $connections;
        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->connections) {
            throw new \Exception('Connections are not set.');
        }

        $result = Result::make();


        foreach ($this->connections as $connection) {
            $index = (int) config("database.redis.{$connection}.database");

            if ($this->getFactDatabase($connection) < $index) {
                return $result->failed(sprintf('"%s" connection has no database #%d', $connection, $index));
            }
        }

        foreach ($this->connections as $connection) {
            if ($this->getConfigDatabase($connection) != 16) {
                return $result->warning(sprintf('"%s" connection has limited databases count', $connection));
            }
        }


        $result->shortSummary('Config is correct.');
        return $result->ok();
    }

    /**
     * @param string $connection
     * @return int
     */
    private function getFactDatabase(string $connection): int
    {
        $result = 0;

        Redis::connection($connection)->set('anourvalar_health_redis_config_check_temp', 1, 'EX', 10);
        foreach (array_keys(Redis::connection($connection)->info('keyspace')) as $key) {
            $key = (int) mb_substr($key, 2);
            if ($key > $result) {
                $result = $key;
            }
        }

        return $result;
    }
    /**
     * @param string $connection
     * @return int
     */
    private function getConfigDatabase(string $connection): int
    {
        return (int) Redis::connection($connection)->config('get', 'databases')['databases'];
    }
}
