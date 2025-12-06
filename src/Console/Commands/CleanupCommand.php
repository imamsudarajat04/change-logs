<?php

namespace Imamsudarajat04\ChangeLogs\Console\Commands;

use Illuminate\Console\Command;
use Imamsudarajat04\ChangeLogs\Services\ChangeLogService;

/**
 * Class CleanupCommand
 *
 * Benefits: Automated cleanup to delete old logs.
 * Can be scheduled via Task Scheduling.
 *
 * @package Imamsudarajat04\ChangeLogs\Console\Commands
 */
class CleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-logs:cleanup 
                            {--days= : Number of days to keep logs (default from config)}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old change logs';

    protected ChangeLogService $service;

    /**
     * Create a new command instance.
     */
    public function __construct(ChangeLogService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days')
            ? (int) $this->option('days')
            : config('change-logs.cleanup.days', 365);

        if (!config('change-logs.cleanup.enabled', false) && !$this->option('force')) {
            $this->warn('Cleanup is disabled in config. Use --force to run anyway.');
            return self::FAILURE;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Delete change logs older than {$days} days?")) {
                $this->info('Cleanup cancelled.');
                return self::FAILURE;
            }
        }

        $cutoffDate = now()->subDays($days)->toDateString();
        $this->info("Cleaning up change logs before {$cutoffDate}...");

        try {
            $deleted = $this->service->cleanup($days);

            $this->newLine();
            $this->info("✓ Successfully deleted {$deleted} old change log(s).");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to cleanup change logs: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}