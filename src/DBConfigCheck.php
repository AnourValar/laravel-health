<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class DBConfigCheck extends Check
{
    /**
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * @var array
     */
    protected array $params = [];

    /**
     * @param string|null $connection
     * @return self
     */
    public function connection(?string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @param array $params
     * @return self
     */
    public function params(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->params) {
            throw new \Exception('Params are not set.');
        }

        $result = Result::make();
        $this->label('DB Config');

        $factparams = $this->getFactParams();
        $errors = [];

        foreach ($this->params as $name => $value) {
            if ($factparams[$name] != $value) {
                $errors[] = sprintf('%s = %s', $name, $factparams[$name]);
            }
        }

        if ($errors) {
            return $result->failed(implode(', ', $errors));
        }

        return $result->ok();
    }

    /**
     * @return array
     */
    private function getFactParams(): array
    {
        $connection = \DB::connection($this->connection);

        if ($connection instanceof \Illuminate\Database\PostgresConnection) {
            return $this->getFactParamsPostgresql($connection);
        }

        return $this->getFactParamsMysql($connection);
    }

    /**
     * @param \Illuminate\Database\PostgresConnection $connection
     * @return array
     */
    private function getFactParamsPostgresql(\Illuminate\Database\PostgresConnection $connection): array
    {
        $result = [];

        foreach ($connection->select('SHOW ALL;') as $item) {
            $result[$item->name] = $item->setting;
        }

        return $result;
    }

    /**
     * @param \Illuminate\Database\MysqlConnection $connection
     * @return array
     * @psalm-suppress UndefinedClass
     */
    private function getFactParamsMysql(\Illuminate\Database\MysqlConnection $connection): array
    {
        $result = [];

        foreach ($connection->select('SHOW VARIABLES;') as $item) {
            $result[$item->Variable_name] = $item->Value;
        }

        return $result;
    }
}
