<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class DirectoryPermissionsCheck extends Check
{
    /**
     * @var array
     */
    protected array $writable = [];

    /**
     * @var array
     */
    protected array $notWritable = [];

    /**
     * @param array|string $paths
     * @return self
     */
    public function writable(array|string $paths): self
    {
        $this->writable = (array) $paths;

        return $this;
    }

    /**
     * @param array|string $paths
     * @return self
     */
    public function notWritable(array|string $paths): self
    {
        $this->notWritable = (array) $paths;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->writable && ! $this->notWritable) {
            throw new \Exception('Paths are not set.');
        }

        $result = Result::make();


        $failed = [];

        foreach ($this->writable as $item) {
            if (is_null($item)) {
                throw new \Exception('Path cannot be null.');
            }

            if (! is_dir($item)) {
                $failed[] = sprintf('%s does not exist.', $item);
            }

            if (! is_writable($item)) {
                $failed[] = sprintf('%s is not writable.', $item);
            }
        }

        foreach ($this->notWritable as $item) {
            if (is_null($item)) {
                throw new \Exception('Path cannot be null.');
            }

            if (! is_dir($item)) {
                $failed[] = sprintf('%s does not exist.', $item);
            }

            if (is_writable($item)) {
                $failed[] = sprintf('%s is writable.', $item);
            }
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(sprintf('%d dir(s) checked.', (count($this->writable) + count($this->notWritable))));
        return $result->ok();
    }
}
