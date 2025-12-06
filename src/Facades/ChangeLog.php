<?php

namespace Imamsudarajat04\ChangeLogs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ChangeLog Facade
 *
 * Benefits: Provides static access to ChangeLogService.
 * Cleaner and easier to use syntax.
 *
 * @package Imamsudarajat04\ChangeLogs\Facades
 *
 * @method static void logCreate(\Illuminate\Database\Eloquent\Model $model)
 * @method static void logUpdate(\Illuminate\Database\Eloquent\Model $model, array $changes)
 * @method static void logDelete(\Illuminate\Database\Eloquent\Model $model, bool $forceDelete = false)
 * @method static void logRestore(\Illuminate\Database\Eloquent\Model $model)
 * @method static \Illuminate\Database\Eloquent\Builder query(array $filters = [])
 * @method static int cleanup(int $days = null)
 *
 * @see \Imamsudarajat04\ChangeLogs\Services\ChangeLogService
 */

class ChangeLog extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'change-logs';
    }
}