<?php

namespace Imamsudarajat04\ChangeLogs\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class InstallCommand
 *
 * Benefits: Simplify the package installation process.
 * Publish config, migrations, and initial setup with 1 command.
 *
 * @package Imamsudarajat04\ChangeLogs\Console\Commands
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'change-logs:install
                            {--force: Overwrite existing files}
                            {--migrations: Only publish migrations}
                            {--config: Only publish config}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Install the ChangeLogs package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Installing ChangeLogs Package...');
        $this->newLine();

        # Publish based on options
        if ($this->option('migrations')) {
            $this->publishMigrations();
        } elseif ($this->option('config')) {
            $this->publishConfig();
        } else {
            $this->publishConfig();
            $this->publishMigrations();
        }

        $this->newLine();
        $this->info('✓ ChangeLogs package installed successfully!');
        $this->newLine();

        # Show next steps
        $this->showNextSteps();

        return self::SUCCESS;
    }

    /**
     * Publish configuration file.
     *
     * @return void
     */
    protected function publishConfig(): void
    {
        $this->info('Publishing configuration...');

        $params = [
            '--provider' => "Imamsudarajat04\ChangeLogs\ChangeLogsServiceProvider",
            '--tag' => 'change-logs-config'
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publish migration file
     *
     * @return void
     */
    protected function publishMigrations(): void
    {
        $this->info('Publishing migrations...');

        $params = [
            '--provider' => "Imamsudarajat04\ChangeLogs\ChangeLogsServiceProvider",
            '--tag' => 'change-logs-migrations'
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    protected function showNextSteps(): void
    {
        $this->comment('Next steps:');
        $this->line('  1. Run migrations: php artisan migrate');
        $this->line('  2. Add HasChangeLogs trait to your models');
        $this->line('  3. (Optional) Customize config/change-logs.php');
        $this->newLine();
        $this->comment('Usage example:');
        $this->line('  use Imamsudarajat04\ChangeLogs\Traits\HasChangeLogs;');
        $this->line('  ');
        $this->line('  class User extends Authenticatable {');
        $this->line('      use HasChangeLogs;');
        $this->line('  }');
        $this->newLine();
        $this->comment('Documentation: https://github.com/imamsudarajat04/change-logs');

    }
}
