<?php

namespace Imamsudarajat04\ChangeLogs\Console\Commands;

use Illuminate\Console\Command;
use Imamsudarajat04\ChangeLogs\Services\ChangeLogService;

/**
 * Class StatsCommand
 *
 * Benefits: Quick overview of statistics change logs via CLI.
 * Useful for monitoring and debugging.
 *
 * @package Imamsudarajat04\ChangeLogs\Console\Commands
 */
class StatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-logs:stats 
                            {--user= : Filter by user ID}
                            {--action= : Filter by action (CREATE, UPDATE, DELETE, RESTORE)}
                            {--model= : Filter by model class}
                            {--days= : Show stats for last N days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display change logs statistics';

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
        $this->info('Change Logs Statistics');
        $this->newLine();

        // Build filters
        $filters = [];

        if ($this->option('user')) {
            $filters['user_id'] = $this->option('user');
        }

        if ($this->option('action')) {
            $filters['action'] = strtoupper($this->option('action'));
        }

        if ($this->option('model')) {
            $filters['model'] = $this->option('model');
        }

        if ($this->option('days')) {
            $days = (int) $this->option('days');
            $filters['start_date'] = now()->subDays($days);
            $filters['end_date'] = now();
        }

        try {
            $stats = $this->service->getStatistics($filters);

            // Display total
            $this->line("Total Logs: <fg=green>{$stats['total']}</>");
            $this->newLine();

            // Display by action
            if (!empty($stats['by_action'])) {
                $this->line('<fg=yellow>By Action:</>');
                $actionData = [];
                foreach ($stats['by_action'] as $action => $count) {
                    $actionData[] = [$action, $count];
                }
                $this->table(['Action', 'Count'], $actionData);
                $this->newLine();
            }

            // Display by user
            if (!empty($stats['by_user'])) {
                $this->line('<fg=yellow>By User:</>');
                $userData = [];
                foreach (array_slice($stats['by_user'], 0, 10, true) as $userId => $count) {
                    $userData[] = [$userId, $count];
                }
                $this->table(['User ID', 'Count'], $userData);
                $this->newLine();
            }

            // Display recent logs
            if (!empty($stats['recent'])) {
                $this->line('<fg=yellow>Recent Logs (Last 10):</>');
                $recentData = [];
                foreach ($stats['recent'] as $log) {
                    $recentData[] = [
                        $log->id,
                        $log->action,
                        class_basename($log->loggable_type),
                        $log->user_id ?? 'System',
                        $log->created_at->diffForHumans(),
                    ];
                }
                $this->table(['ID', 'Action', 'Model', 'User', 'Time'], $recentData);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to get statistics: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}