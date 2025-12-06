# Laravel Change Logs

[![Latest Version on Packagist](https://img.shields.io/packagist/v/imamsudarajat04/laravel-change-logs.svg?style=flat-square)](https://packagist.org/packages/imamsudarajat04/laravel-change-logs)
[![Total Downloads](https://img.shields.io/packagist/dt/imamsudarajat04/laravel-change-logs.svg?style=flat-square)](https://packagist.org/packages/imamsudarajat04/laravel-change-logs)

A comprehensive change logs tracking package for Laravel 11+ that automatically tracks all model changes (create, update, delete, restore) with detailed audit trails.

## Features

✨ **Automatic Tracking** - Zero configuration needed, just add a trait
📝 **Detailed Logging** - Track field-level changes with old/new values
🔍 **Flexible Querying** - Built-in scopes and filters
👤 **User Context** - Automatically captures user, IP, and user agent
🏷️ **Tagging Support** - Categorize logs with custom tags
⚡ **Queue Support** - Async logging for better performance
🧹 **Auto Cleanup** - Scheduled cleanup of old logs
🎯 **Polymorphic** - Works with any Eloquent model
🔒 **Security** - Excludes sensitive fields (passwords, tokens)
📊 **Statistics** - Built-in analytics and reporting

## Requirements

- PHP 8.2+
- Laravel 11.0+
- MySQL / PostgreSQL / SQLite

## Installation

### 1. Install via Composer
```bash
composer require imamsudarajat04/laravel-change-logs
```

### 2. Install Package
```bash
php artisan change-logs:install
```

This will publish:
- Configuration file: `config/change-logs.php`
- Migration file: `database/migrations/xxx_create_change_logs_table.php`

### 3. Run Migrations
```bash
php artisan migrate
```

## Basic Usage

### Add Trait to Model
```php
use Imamsudarajat04\ChangeLogs\Traits\HasChangeLogs;

class User extends Authenticatable
{
    use HasChangeLogs;
}
```

That's it! Now all changes to `User` model will be automatically tracked.

### Access Change Logs
```php
// Get all change logs for a model
$user = User::find(1);
$logs = $user->changeLogs; // Returns collection of ChangeLog

// Get recent logs
$recentLogs = $user->recentChangeLogs(5);

// Get logs by action
$updateLogs = $user->changeLogsByAction('UPDATE');

// Get logs for specific field
$emailChanges = $user->fieldChangeLogs('email');

// Check if model has logs
if ($user->hasChangeLogs()) {
    // ...
}

// Get last change
$lastChange = $user->lastChangeLog();
```

### Query Change Logs Directly
```php
use Imamsudarajat04\ChangeLogs\Models\ChangeLog;

// Using scopes
$logs = ChangeLog::action('UPDATE')->get();
$logs = ChangeLog::forModel(User::class)->byUser(1)->get();
$logs = ChangeLog::dateRange('2024-01-01', '2024-12-31')->get();
$logs = ChangeLog::withTag('important')->get();

// Chain multiple scopes
$logs = ChangeLog::action('UPDATE')
    ->byUser(1)
    ->dateRange('2024-01-01', '2024-12-31')
    ->limit(10)
    ->get();
```

### Using Service/Facade
```php
use Imamsudarajat04\ChangeLogs\Facades\ChangeLog;

// Query with filters
$logs = ChangeLog::query([
    'action' => 'UPDATE',
    'user_id' => 1,
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
])->paginate(20);

// Get statistics
$stats = ChangeLog::getStatistics();
// Returns: ['total' => 100, 'by_action' => [...], 'by_user' => [...], 'recent' => [...]]

// Cleanup old logs
ChangeLog::cleanup(365); // Delete logs older than 365 days
```

## Advanced Usage

### Customize Excluded Fields
```php
class User extends Authenticatable
{
    use HasChangeLogs;

    public function getChangeLogExcludedFields(): array
    {
        return ['password', 'remember_token', 'api_token'];
    }
}
```

### Custom Descriptions
```php
class User extends Authenticatable
{
    use HasChangeLogs;

    public function getChangeLogDescription(string $action): ?string
    {
        return match($action) {
            'CREATE' => "User {$this->name} was registered",
            'UPDATE' => "User {$this->name} updated their profile",
            'DELETE' => "User {$this->name} was deleted",
            default => null,
        };
    }
}
```

### Custom Tags
```php
class Order extends Model
{
    use HasChangeLogs;

    public function getChangeLogTags(string $action): array
    {
        return match($action) {
            'CREATE' => ['order', 'new', 'customer-action'],
            'UPDATE' => ['order', 'modified'],
            default => ['order'],
        };
    }
}
```

### Conditional Logging
```php
class User extends Authenticatable
{
    use HasChangeLogs;

    public function shouldLogChanges(string $action): bool
    {
        // Don't log test users
        if (str_contains($this->email, '@test.com')) {
            return false;
        }

        return parent::shouldLogChanges($action);
    }
}
```

### Temporarily Disable Logging
```php
// For single model
User::withoutChangeLogs(function() {
    User::where('active', 0)->update(['status' => 'inactive']);
});

// For specific operations
config(['change-logs.enabled' => false]);
// Your operations here
config(['change-logs.enabled' => true]);
```

## Artisan Commands

### Install Package
```bash
php artisan change-logs:install

# Options:
--migrations    # Only publish migrations
--config       # Only publish config
--force        # Overwrite existing files
```

### View Statistics
```bash
php artisan change-logs:stats

# With filters:
php artisan change-logs:stats --user=1
php artisan change-logs:stats --action=UPDATE
php artisan change-logs:stats --model="App\Models\User"
php artisan change-logs:stats --days=7
```

### Cleanup Old Logs
```bash
php artisan change-logs:cleanup

# Options:
--days=90     # Custom retention days
--force       # Skip confirmation
```

### Prune with Filters
```bash
php artisan change-logs:prune

# Options:
--model="App\Models\User"  # Prune specific model
--action=DELETE            # Prune specific action
--pretend                  # Dry run
```

## Configuration

Publish and edit `config/change-logs.php`:
```php
return [
    // Enable/disable globally
    'enabled' => env('CHANGE_LOGS_ENABLED', true),

    // Logging strategy
    'log_per_field' => false, // true = separate log per field, false = bulk

    // Track additional context
    'track_ip' => true,
    'track_user_agent' => true,

    // Exclude fields
    'hidden_fields' => ['password', 'remember_token'],
    'exclude_timestamps' => true,

    // Queue configuration
    'queue' => [
        'enabled' => false,
        'connection' => null,
        'queue' => 'default',
    ],

    // Auto cleanup
    'cleanup' => [
        'enabled' => false,
        'days' => 365,
    ],
];
```

## Task Scheduling

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Cleanup logs monthly
    $schedule->command('change-logs:cleanup --force')->monthly();
    
    // Or use prune
    $schedule->command('change-logs:prune')->monthly();
}
```

## Testing
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email imamsudarajat708@gmail.com instead of using the issue tracker.

## Credits

- [Imam Sudarajat](https://github.com/imamsudarajat04)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.