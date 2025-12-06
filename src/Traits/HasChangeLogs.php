<?php

namespace Imamsudarajat04\ChangeLogs\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\Observers\ChangeLogObserver;

/**
 * Trait HasChangeLog
 *
 * Benefit: Automatically adding change tracking capabilities to the Model.
 * Simply add this trait to the model you want to track.
 *
 * @package Imamsudarajat04\ChangeLogs\Traits
 */
trait HasChangeLogs
{
    /**
     * Boot the trait.
     *
     * Benefits: Automatically registers observers for tracking changes
     * without the need for manual configuration in each model.
     *
     * @return void
     */
    protected static function bootHasChangeLogs(): void
    {
        static::observe(ChangeLogObserver::class);
    }

    /**
     * Get all change logs for the model
     *
     * Benefits: Simplifies access to all change logs via relationships.
     * Example: $user->changeLogs()->get()
     *
     * @return MorphMany
     */
    public function changeLogs(): MorphMany
    {
        return $this->morphMany(ChangeLog::class, 'loggable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get change log by action.
     *
     * Benefits: Quick filter to view logs based on specific actions.
     * Example: $user->changeLogsByAction(‘UPDATE’)
     *
     * @param string $action
     * @return MorphMany
     */
    public function changeLogsByAction(string $action): MorphMany
    {
        return $this->changeLogs()->where('action', strtoupper($action));
    }

    /**
     * Get recent change logs.
     *
     * Benefits: Quickly retrieve the latest logs without having to perform manual queries.
     * Example: $user->recentChangeLogs(10)
     *
     * @param int $limit
     * @return MorphMany
     */
    public function recentChangeLogs(int $limit = 20): MorphMany
    {
        return $this->changeLogs()->limit($limit);
    }

    /**
     * Get change logs by user.
     *
     * Benefits: Filter logs based on who made the changes.
     * Example: $user->changeLogsByUser($adminId)
     *
     * @param $userId
     * @return MorphMany
     */
    public function changeLogsByUser($userId): MorphMany
    {
        return $this->changeLogs()->where('user_id', $userId);
    }

    /**
     * Get change logs for specific field.
     *
     * Benefits: Track changes to specific fields only (e.g., email, status).
     * Example: $user->fieldChangeLogs(‘email’)
     *
     * @param string $fieldName
     * @return MorphMany
     */
    public function fieldChangeLogs(string $fieldName): MorphMany
    {
        return $this->changeLogs()->where('field_name', $fieldName);
    }

    /**
     * Check if model has any change logs.
     *
     * Benefits: Quickly check whether the model has ever undergone changes.
     * Example: if($user->hasChangeLogs()) { ... }
     *
     * @return bool
     */
    public function hasChangeLogs(): bool
    {
        return $this->changeLogs()->exists();
    }

    /**
     * Get the last change log.
     *
     * Benefits: Quickly adopt the latest changes.
     * Example : $lastChange = $user->lastChangeLog()
     *
     * @return ChangeLog|null
     */
    public function lastChangeLog(): ?ChangeLog
    {
        return $this->changeLogs()
            ->orderByDesc($this->getKeyName())
            ->first();
    }

    /**
     * Get fields that should be excluded from change logs.
     *
     * Benefits: Customize which fields per model do not need to be logged
     * (passwords, tokens, etc.). Override this method in the model if necessary.
     *
     * @return array
     */
    public function getChangeLogExcludedFields(): array
    {
        return array_merge(
            config('change-logs.hidden_fields', []),
            $this->hidden ?? [],
            config('change-logs.excluded_timestamps', true)
                ? ['created_at', 'updated_at', 'deleted_at']
                : []
        );
    }

    /**
     * Get custom description for change log.
     *
     *  Benefits: Override this method for a custom description per action.
     *  Example: “User John updated their profile picture”
     *
     * @param string $action
     * @return string|null
     */
    public function getChangeLogDescription(string $action): ?string
    {
        return null;
    }

    /**
     * Get tags for change log.
     *
     * Benefits: Add automatic tags for categorization/filtering.
     * Override in the model for custom tags.
     *
     * @param string $action
     * @return array
     */
    public function getChangeLogTags(string $action): array
    {
        return [];
    }

    /**
     * Determine if changes should be logged for this action.
     *
     * Benefits: Control per model whether certain actions need to be logged.
     * Override for custom logic.
     *
     * @param string $action
     * @return bool
     */
    public function shouldLogChanges(string $action): bool
    {
        if (!config('change-logs.enabled', true)) {
            return false;
        }

        $actionKey = strtolower($action);
        return config("change-logs.track_actions.{$actionKey}", true);
    }

    /**
     * Get the formatted change log data.
     *
     * Benefits: Transform data before saving it to the log.
     * Override for custom formatting.
     *
     * @param $value
     * @param string $field
     * @return mixed
     */
    public function formatChangeLogValue($value, string $field): mixed
    {
        # Handle special cases
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if(is_object($value) && method_exists($value, 'toArray')) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * Disable change logs temporarily
     *
     * Benefits: Temporarily disable logging for bulk operations without logs.
     * Example: $model->withoutChangeLogs(function() use ($model) { ... })
     *
     * @param callable $callback
     * @return mixed
     */
    public static function withoutChangeLogs(callable $callback): mixed
    {
        static::unregisterChangeLogObserver();

        try {
            return $callback();
        } finally {
            static::registerChangeLogObserver();
        }
    }

    /**
     * Unregister the change log observer.
     *
     * @return void
     */
    public static function unregisterChangeLogObserver(): void
    {
        $class = static::class;
        unset(static::$observables[$class]);
    }

    /**
     * Register the change log observer.
     *
     * @return void
     */
    protected static function registerChangeLogObserver(): void
    {
        static::observe(ChangeLogObserver::class);
    }
}