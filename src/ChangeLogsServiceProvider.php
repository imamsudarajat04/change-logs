<?php

namespace Imamsudarajat04\ChangeLogs;

use Imamsudarajat04\ChangeLogs\Console\Commands\StatsCommand;
use Illuminate\Support\ServiceProvider;
use Imamsudarajat04\ChangeLogs\Console\Commands\CleanupCommand;
use Imamsudarajat04\ChangeLogs\Console\Commands\InstallCommand;
use Imamsudarajat04\ChangeLogs\Services\ChangeLogService;

class ChangeLogsServiceProvider extends ServiceProvider
{
    /**
     *  Benefits: Registering services in Laravel containers
     *  so that it can be injected as a dependency.
     *
     * @return void
     */
    public function register(): void
    {
        # Merge Config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/change-logs.php',
            'change-logs'
        );

        # Register singleton service
        $this->app->singleton('change-logs', function ($app) {
            return new ChangeLogService();
        });

        # Register alias
        $this->app->alias('change-logs', ChangeLogService::class);
    }

    /**
     * Benefits: Publish assets (config, migration) and
     * register commands for this package.
     * @return void
     */
    public function boot(): void
    {
        # Publish config
        $this->publishes([
            __DIR__ . '/../config/change-logs.php' => config_path('change-logs.php'),
        ], 'change-logs-config');

        # Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/create_change_logs_table.php' =>
                database_path('migrations/' . date('Y_m_d_His') . '_create_change_logs_table.php'),
        ], 'change-logs-migrations');

        # Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        # Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                CleanupCommand::class,
                StatsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'change-logs',
            ChangeLogService::class,
            InstallCommand::class,
            CleanupCommand::class,
            StatsCommand::class,
        ];
    }
}