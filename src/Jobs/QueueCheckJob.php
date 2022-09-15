<?php

namespace AnourValar\LaravelHealth\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueueCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $timestamp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->timestamp = now()->timestamp;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     * @return void
     */
    public function handle()
    {
        $queueCheck = \Spatie\Health\Facades\Health::registeredChecks()->first(
            fn (\Spatie\Health\Checks\Check $check) => $check instanceof \AnourValar\LaravelHealth\QueueCheck
        );

        if (! $queueCheck) {
            throw new \Exception("In order to use this command, you should register the `AnourValar\LaravelHealth\QueueCheck`");
        }

        \Cache::set($queueCheck->getCacheKey(), ['run' => now()->timestamp, 'delay' => now()->timestamp - $this->timestamp]);
    }
}
