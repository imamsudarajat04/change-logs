<?php

namespace Imamsudarajat04\ChangeLogs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class CreateChangeLogJob
 *
 * Benefits: Async processing for creating change logs.
 * Improve performance by offloading to queue workers.
 */
class CreateChangeLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $logData = [];

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 30;

    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        config('change-logs.user_model')::query()->create($this->logData);
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function fail(Throwable $exception): void
    {
        # Log failure
        Log::error('Failed to create change log:', [
            'data'    => $this->logData,
            'message' => $exception->getMessage(),
        ]);
    }
}