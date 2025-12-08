<?php

namespace Imamsudarajat04\ChangeLogs\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Imamsudarajat04\ChangeLogs\Jobs\CreateChangeLogJob;
use Imamsudarajat04\ChangeLogs\Models\ChangeLog;
use Imamsudarajat04\ChangeLogs\Enums\RecordAction;


/**
 * Class ChangeLogService
 *
 * Benefits: Business logic for creating change logs.
 * Separating logic from observers for maintainability and testability.
 *
 * @package Imamsudarajat04\ChangeLogs\Services
 */
class ChangeLogService
{
    /**
     * Log a create action.
     *
     * Benefits: Records the creation of new records with all their attributes.
     *
     * @param Model $model
     * @return void
     */
   public function logCreate(Model $model): void
   {
        $attributes = $this->getLoggableAttributes($model);

        $logData = [
            'loggable_type' => get_class($model),
            'loggable_id'   => $model->getKey(),
            'action'        => RecordAction::CREATE->value,
            'field_column'  => null,
            'old_value'     => null,
            'new_value'     => $attributes,
            'date'          => now()->toDateString(),
            'description'   => $this->getDescription($model, RecordAction::CREATE->value),
            'tags'          => $this->getTags($model, RecordAction::CREATE->value),
        ];

        $this->createLog($logData);
   }

    /**
     * Log an update action.
     *
     * Benefits: Records changes in detail per field.
     * Options: 1 log per field or 1 log for all changes.
     *
     * @param Model $model
     * @param array $changes
     * @return void
     */
   public function logUpdate(Model $model, array $changes): void
   {
       $excludedFields = $this->getExcludedFields($model);

       # Filter out excluded fields
       $changes = array_diff_key($changes, array_flip($excludedFields));

       if (empty($changes)) {
           return;
       }

       # Option 1: Create separate log for each changed field
       if (config('change-logs.log_per_field', false)) {
            foreach ($changes as $field => $newValue) {
                $this->logFieldChange($model, $field, $newValue);
            }
       } else {
           # Option 2: Create single log with all changes (default)
           $this->logBulkChanges($model, $changes);
       }
   }

    /**
     * Log a single field change.
     *
     * Benefits: Granular tracking per field for detailed auditing.
     *
     * @param Model $model
     * @param string $field
     * @param $newValue
     * @return void
     */
   protected function logFieldChange(Model $model, string $field, $newValue): void
   {
        $oldValue = $model->getOriginal($field);

        $logData = [
            'loggable_type' => get_class($model),
            'loggable_id'   => $model->getKey(),
            'action'        => RecordAction::UPDATE->value,
            'field_column'  => $field,
            'old_value'     => $this->formatValue($model, $field, $oldValue),
            'new_value'     => $this->formatValue($model, $field, $newValue),
            'date'          => now()->toDateString(),
            'description'   => $this->getDescription($model, RecordAction::UPDATE->value, $field),
            'tags'          => $this->getTags($model, RecordAction::UPDATE->value),
        ];

        $this->createLog($logData);
   }

    /**
     * Log multiple changes in single entry.
     *
     * Benefits: Efficient for multiple field changes, reduces DB rows.
     *
     * @param Model $model
     * @param array $changes
     * @return void
     */
   protected function logBulkChanges(Model $model, array $changes): void
   {
       $oldValue = [];
       $newValues = [];

       foreach ($changes as $field => $newValueRaw) {
           $oldValue[$field] = $this->formatValue($model, $field, $model->getOriginal($field));
           $newValues[$field] = $this->formatValue($model, $field, $newValueRaw);
       }

       $logData = [
           'loggable_type' => get_class($model),
           'loggable_id'   => $model->getKey(),
           'action'        => RecordAction::UPDATE->value,
           'field_column'  => null,
           'old_value'     => $oldValue,
           'new_value'     => $newValues,
           'date'          => now()->toDateString(),
           'description'   => $this->getDescription($model, RecordAction::UPDATE->value),
           'tags'          => $this->getTags($model, RecordAction::UPDATE->value),
       ];

       $this->createLog($logData);
   }

    /**
     * Log a delete action.
     *
     * Benefits: Records the last snapshot before data is deleted.
     *
     * @param Model $model
     * @param bool $forceDelete
     * @return void
     */
   public function logDelete(Model $model, bool $forceDelete = false): void
   {
       $attributes = $this->getLoggableAttributes($model);

       $logData = [
           'loggable_type' => get_class($model),
           'loggable_id'   => $model->getKey(),
           'action'        => RecordAction::DELETE->value,
           'field_column'  => null,
           'old_value'     => $attributes,
           'new_value'     => null,
           'date'          => now()->toDateString(),
           'description'   => $this->getDescription($model, RecordAction::DELETE->value) . ($forceDelete ? ' (Permanent)' : ''),
           'tags'          => array_merge($this->getTags($model, 'DELETE'), $forceDelete ? ['force_delete'] : []),
       ];

       $this->createLog($logData);
   }

    /**
     * Log a restore action.
     *
     * Benefits: Tracking when soft deleted records are restored.
     *
     * @param Model $model
     * @return void
     */
   public function logRestore(Model $model): void
   {
        $attributes = $this->getLoggableAttributes($model);

        $logData = [
            'loggable_type' => get_class($model),
            'loggable_id'   => $model->getKey(),
            'action'        => RecordAction::RESTORE->value,
            'field_column'  => null,
            'old_value'     => null,
            'new_value'     => $attributes,
            'date'          => now()->toDateString(),
            'description'   => $this->getDescription($model, RecordAction::RESTORE->value),
            'tags'          => $this->getTags($model, RecordAction::RESTORE->value),
        ];

        $this->createLog($logData);
   }

    /**
     * Create the change log entry.
     *
     * Benefits: Central point for creating logs. Support queue for performance.
     *
     * @param array $logData
     * @return void
     */
   protected function createLog(array $logData): void
   {
       # Add user information
       $logData = array_merge($logData, $this->getUserInformation());

       # Auto set date if not provided
       if (!isset($logData['date'])) {
           $logData['date'] = now()->toDateString();
       }

       # Check if should be queued
       if (config('change-logs.queue.enabled', false)) {
           dispatch(new CreateChangeLogJob($logData))
               ->onConnection(config('change-logs.queue.connection'))
               ->onQueue(config('change-logs.queue.queue'));
       } else {
           config('change-logs.user_model')::query()->create($logData);
       }
   }

    /**
     * Get user information (user_id, ip, user_agent)
     *
     * Benefits: Capture context of who made the change and where it was made.
     *
     * @return array
     */
   protected function getUserInformation(): array
   {
        $data = [
            'user_id' => Auth::id(),
        ];

       # Web request context
       if (config('change-logs.track_ip', true)) {
           $data['ip_address'] = Request::ip();
       }

       if (config('change-logs.track_user_agent', true)) {
           $data['user_agent'] = Request::userAgent();
       }

       if (config('change-logs.track_method', true)) {
           $data['method'] = strtoupper(Request::method());
       }

       if (config('change-logs.track_endpoint', true)) {
           # Store endpoint only (with leading slash)
           $endpoint = Request::path();
           $data['endpoint'] = $endpoint === '/' ? '/' : '/' . $endpoint;
       }

        return $data;
   }

    /**
     * Get loggable attributes from model.
     *
     * Benefits: Filter attributes that can be logged, exclude sensitive data.
     *
     * @param Model $model
     * @return array
     */
   protected function getLoggableAttributes(Model $model): array
   {
        $attributes = $model->getAttributes();
        $excludedFields = $this->getExcludedFields($model);

        return array_diff_key($attributes, array_flip($excludedFields));
   }

    /**
     * Get excluded fields from model.
     *
     * Benefits: Combine config-level and model-level excluded fields.
     *
     * @param Model $model
     * @return array
     */
   protected function getExcludedFields(Model $model): array
   {
        if (method_exists($model, 'getChangeLogExcludedFields')) {
            return $model->getChangeLogExcludedFields();
        }

        $excluded = config('change-logs.hidden_fields', []);

        if (config('change-logs.exclude_timestamps', true)) {
            $excluded = array_merge($excluded, ['created_at', 'updated_at', 'deleted_at']);
        }

        return $excluded;
   }

    /**
     * Format value for logging.
     *
     * Benefits: Transform values before saving (dates, objects, etc.).
     *
     * @param Model $model
     * @param string $field
     * @param $value
     * @return mixed
     */
   protected function formatValue(Model $model, string $field, $value): mixed
   {
        if (method_exists($model, 'formatChangeLogValue')) {
            return $model->formatChangeLogValue($value, $field);
        }

        # Handle dates
       if ($value instanceof \DateTimeInterface) {
           return $value->format('Y-m-d H:i:s');
       }

       # Handle objects with toArray
       if (is_object($value) && method_exists($value, 'toArray')) {
           return $value->toArray();
       }

       # Handle boolean
       if (is_bool($value)) {
           return $value ? 'true' : 'false';
       }

       return $value;
   }

    /**
     * Get description for the log entry.
     *
     * Benefits: Human-readable description, can be customized per model.
     *
     * @param Model $model
     * @param string $action
     * @param string|null $field
     * @return string|null
     */
   protected function getDescription(Model $model, string $action, ?string $field = null): ?string
   {
       if (method_exists($model, 'getChangeLogDescription')) {
           return $model->getChangeLogDescription($action);
       }

       return null;
   }

    /**
     * Get tags for the log entry.
     *
     * Benefits: Categorization for filtering and reporting.
     *
     * @param Model $model
     * @param string $action
     * @return array
     */
   protected function getTags(Model $model, string $action): array
   {
        if (method_exists($model, 'getChangeLogTags')) {
            return $model->getChangeLogTags($action);
        }

        return [];
   }

    /**
     * Query change logs with filters.
     *
     * @param array $filters Supported keys: model, action, user_id, start_date, end_date, tag, loggable_id, loggable_type
     * @return Builder<ChangeLog>
    */
   public function query(array $filters = []): Builder
   {
       /** @var Builder<ChangeLog> $query */
       $query = ChangeLog::query();

       # Filter by model
       if (isset($filters['model'])) {
           $query->forModel($filters['model']);
       }

       # Filter by action
       if (isset($filters['action'])) {
           $query->action($filters['action']);
       }

       # Filter by user
       if (isset($filters['user_id'])) {
           $query->byUser($filters['user_id']);
       }

       # Filter by date range
       if (isset($filters['start_date']) && isset($filters['end_date'])) {
           $query->dateRange($filters['start_date'], $filters['end_date']);
       }

       # Filter bu tag
       if (isset($filters['tag'])) {
           $query->withTag($filters['tag']);
       }

       # Filter by loggable
       if (isset($filters['loggable_id']) && isset($filters['loggable_type'])) {
           $query->where('loggable_id', $filters['loggable_id'])
               ->where('loggable_type', $filters['loggable_type']);
       }

       return $query->orderBy('created_at', 'desc');
   }

    /**
     * Get statistics for change logs.
     *
     * Benefits: Dashboard metrics - total logs, by action, by user, etc.
     */
    public function getStatistics(array $filters = []): array
    {
        $query = $this->query($filters);

        return [
            'total' => $query->count(),
            'by_action' => $query->clone()->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'by_user' => $query->clone()->selectRaw('user_id, count(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->pluck('count', 'user_id')
                ->toArray(),
            'recent' => $query->clone()->limit(10)->get(),
        ];
    }

    /**
     * Cleanup old change logs.
     *
     * Benefits: Maintenance - delete old logs to optimize storage.
     */
    public function cleanup(?int $days = null): int
    {
        if (!config('change-logs.cleanup.enabled', true)) {
            return 0;
        }

        $days = $days ?? config('change-logs.cleanup.days', 365);
        $cutoffDate = now()->subDays($days)->toDateString();

        # Much simpler and more reliable query
        $deleted = ChangeLog::query()
            ->where('date', '<', $cutoffDate)
            ->delete();

        \Log::info('Change logs cleanup completed', [
            'days' => $days,
            'cutoff_date' => $cutoffDate,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Cleanup logs by date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function cleanupByDateRange(string $startDate, string $endDate): int
    {
        return ChangeLog::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->delete();
    }

    /**
     * Cleanup logs before specific date.
     *
     * @param string $date
     * @return int
     */
    public function cleanupBeforeDate(string $date): int
    {
        return ChangeLog::query()
            ->where('date', '<', $date)
            ->delete();
    }
}