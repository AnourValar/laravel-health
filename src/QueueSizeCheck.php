<?php

namespace AnourValar\LaravelHealth;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class QueueSizeCheck extends Check
{
    /**
     * @var array
     */
    protected array $queues = [];

    /**
     * @param array $queue
     * @return self
     */
    public function add(array $queue): self
    {
        $this->queues[] = $queue;

        return $this;
    }

    /**
     * @see https://spatie.be/docs/laravel-health/v1/basic-usage/creating-custom-checks
     *
     * @return \Spatie\Health\Checks\Result
     */
    public function run(): Result
    {
        if (! $this->queues) {
            throw new \Exception('Queues are not set.');
        }

        $result = Result::make();


        $failed = [];
        foreach ($this->queues as $queue) {
            $size = $this->getSize($queue['connection'], $queue['name']);
            if ($size > $queue['max_size']) {
                $queue['connection'] ??= config('queue.default');
                $queue['name'] ??= config("queue.connections.{$queue['connection']}.queue");

                $failed[] = sprintf('%s->%s size: %d', $queue['connection'], $queue['name'], $size);
            }
        }


        if ($failed) {
            return $result->failed(implode(' ', $failed));
        }

        $result->shortSummary(sprintf('%d queue(s) size checked.', count($this->queues)));
        return $result->ok();
    }

    /**
     * @param string $connection
     * @param string $name
     * @return int
     */
    protected function getSize(?string $connection, ?string $name): int
    {
        return \Queue::connection($connection)->size($name);
    }
}
