<?php

namespace Imamsudarajat04\ChangeLogs\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Imamsudarajat04\ChangeLogs\Services\ChangeLogService;
use Imamsudarajat04\ChangeLogs\enums\RecordAction;

/**
 * Class ChangeLogObserver
 *
 * Benefits: Automatically captures model events (created, updated, deleted)
 * and records changes to the database in real time.
 *
 * @package Imamsudarajat04\ChangeLos\Observers
 */
class ChangeLogObserver
{
    protected ChangeLogService $changeLogService;

    public function __construct(ChangeLogService $changeLogService)
    {
        $this->changeLogService = $changeLogService;
    }

    /**
     * Handle the Model "created" event.
     *
     * Benefits: Tracking when a new record is created.
     * Records all new attributes as “new_value”.
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model): void
    {
        if (!$this->shouldLog($model, RecordAction::CREATE->value)) {
            return;
        }

        $this->changeLogService->logCreate($model);
    }

    /**
     * Handle the Model "updated" event.
     *
     * Benefits: Tracking data changes. Only records fields that have changed
     * by comparing old vs. new values (getOriginal vs. getAttribute).
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model): void
    {
        if (!$this->shouldLog($model, RecordAction::UPDATE->value)) {
            return;
        }

        # Get only the changed attributes
        $changes = $model->getDirty();

        if (empty($changes)) {
            return;
        }

        $this->changeLogService->logUpdate($model, $changes);
    }

    /**
     * Handle the Model "deleted" event.
     *
     * Benefits: Tracking when records are deleted.
     * Records all attributes before deletion.
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model): void
    {
        if (!$this->shouldLog($model, RecordAction::DELETE->value)) {
            return;
        }

        $this->changeLogService->logDelete($model);
    }

    /**
     * Handle the Modal "restored" event (for soft deletes)
     *
     * Benefits: Tracking when records are restored from soft delete.
     * Important for a complete audit trail.
     *
     * @param Model $model
     * @return void
     */
    public function restored(Model $model): void
    {
        if (!$this->shouldLog($model, RecordAction::RESTORE->value)) {
            return;
        }

        $this->changeLogService->logRestore($model);
    }

    /**
     * Handle the Model "forceDeleted" event.
     *
     * Benefits: Tracking permanent deletion (hard delete).
     * Records the last data before permanent deletion.
     *
     * @param Model $model
     * @return void
     */
    public function forceDeleted(Model $model): void
    {
        if (!$this->shouldLog($model, RecordAction::DELETE->value)) {
            return;
        }

        $this->changeLogService->logDelete($model);
    }

    /**
     * Determine if the model changes should be logged.
     *
     *  Benefits: Central point for validating whether changes need to be logged.
     *  Check the config and shouldLogChanges method in the model.
     *
     * @param Model $model
     * @param string $action
     * @return bool
     */
    protected function shouldLog(Model $model, string $action): bool
    {
        if (method_exists($model, 'shouldLogChanges')) {
            return $model->shouldLogChanges($action);
        }

        return true;
    }
}