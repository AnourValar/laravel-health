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
            $sizeTotal = $this->getSizeTotal($queue['connection'], $queue['name']);
            $sizeDelayed = $this->getSizeDelayed($queue['connection'], $queue['name']);

            if ($sizeTotal > $queue['max_size']) {
                $queue['connection'] ??= config('queue.default');
                $queue['name'] ??= config("queue.connections.{$queue['connection']}.queue");

                $failed[] = sprintf('%s->%s size: %d [%d]', $queue['connection'], $queue['name'], $sizeTotal, $sizeDelayed);
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
    protected function getSizeTotal(?string $connection, ?string $name): int
    {
        return \Queue::connection($connection)->size($name);
    }

    /**
     * @param string $connection
     * @param string $name
     * @return int
     */
    protected function getSizeDelayed(?string $connection, ?string $name): int
    {
        return \Queue::connection($connection)->delayedSize($name);
    }
}
