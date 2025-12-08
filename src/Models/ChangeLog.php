<?php

namespace Imamsudarajat04\ChangeLogs\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;
use Imamsudarajat04\ChangeLogs\Enums\Table;

/**
 *  Class ChangeLog
 *
 * @package Imamsudarajat04\ChangeLogs\Models
 *
 * @property string id
 * @property string loggable_type
 * @property string loggable_id
 * @property string action
 * @property string field_column
 * @property array|mixed old_value
 * @property array|mixed new_value
 * @property string user_id
 * @property string ip_address
 * @property string user_agent
 * @property string description
 * @property string method
 * @property string endpoint
 * @property array|mixed tags
 * @property Carbon date
 * @property Carbon created_at
 * @property Carbon updated_at
 * @method static Builder|ChangeLog action(string $action)
 * @method static Builder|ChangeLog forModel(string $modelClass)
 * @method static Builder|ChangeLog byUser($userId)
 * @method static Builder|ChangeLog dateRange($startDate, $endDate)
 * @method static Builder|ChangeLog onDate($date)
 * @method static Builder|ChangeLog withInDays(int $days)
 * @method static Builder|ChangeLog olderThanDays(int $days)
 * @method static Builder|ChangeLog today()
 * @method static Builder|ChangeLog yesterday()
 * @method static Builder|ChangeLog thisWeek()
 * @method static Builder|ChangeLog thisMonth()
 * @method static Builder|ChangeLog withTag(string $tag)
 */
class ChangeLog extends Model
{
    use HasUuids;

    protected $table = Table::CHANGE_LOGS->value;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'action',
        'field_column',
        'old_value',
        'new_value',
        'user_id',
        'ip_address',
        'user_agent',
        'method',
        'endpoint',
        'description',
        'tags',
        'date'
    ];

    protected $casts = [
        'old_value'  => 'array',
        'new_value'  => 'array',
        'tags'       => 'array',
        'date'       => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Set date before saving (works for both create and update)
        static::saving(function ($model) {
            if (empty($model->date)) {
                $model->date = now()->toDateString();
            }
        });
    }

    /**
     * Get the date attribute.
     * Returns today's date if null.
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? \Carbon\Carbon::parse($value) : today(),
        );
    }

    /**
     * Get the parent loggable model.
     *
     * @return MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the change.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('change-logs.user_model', 'App\\Models\\User'), 'user_id');
    }

    /**
     * Scope to filter by action.
     *
     * @param Builder $query
     * @param string $action
     * @return Builder
     */
    public function scopeAction(Builder $query, string $action): Builder
    {
        return $query->where('action', strtoupper($action));
    }

    /**
     * Scope to filter by loggable type.
     *
     * @param Builder $query
     * @param string $modelClass
     * @return Builder
     */
    public function scopeForModel(Builder $query, string $modelClass): Builder
    {
        return $query->where('loggable_type', $modelClass);
    }

    /**
     * Scope to filter by user.
     *
     * @param Builder $query
     * @param $userId
     * @return Builder
     */
    public function scopeByUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range.
     *
     * @param Builder $query
     * @param $startDate
     * @param $endDate
     * @return Builder
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return  $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by specific date.
     *
     * @param Builder $query
     * @param $data
     * @return Builder
     */
    public function scopeOnDate(Builder $query, $data): Builder
    {
        return $query->whereDate('date', $data);
    }

    /**
     * Scope to get logs within last N days.
     *
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeWithinDays(Builder $query, int $days): Builder
    {
        $startDate = now()->subDays($days)->toDateString();
        return $query->where('date', '>=', $startDate);
    }

    /**
     * Scope to get logs older than N days.
     *
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeOlderThanDays(Builder $query, int $days): Builder
    {
        $cutoffDate = now()->subDays($days)->toDateString();
        return $query->where('date', '<', $cutoffDate);
    }

    /**
     * Scope to filter by tags.
     *
     * @param Builder $query
     * @param string $tag
     * @return Builder
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope to get today's logs.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope to get yesterday's logs.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeYesterday(Builder $query): Builder
    {
        return $query->whereDate('date', today()->subDay());
    }

    /**
     * Scope to get this week's logs.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('date', [
            now()->startOfWeek()->toDateString(),
            now()->endOfWeek()->toDateString(),
        ]);
    }

    /**
     * Scope to get this month's logs.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('date', [
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString(),
        ]);
    }
}