<?php

namespace App\Observers;

use App\Models\MaintenanceRecord;
use App\Services\MaintenanceService;
use Illuminate\Support\Facades\DB;

class MaintenanceRecordObserver
{
    /**
     * Automated status update rule.
     *
     * The moment a maintenance record's status is set to "Completed" or
     * "Cancelled", the corresponding bicycle may be released back to
     * "Available" in the Bicycle Inventory — but only if no other active
     * (scheduled / in_progress) maintenance records remain for that unit.
     *
     * Reacting to the model transition keeps the rule automatic for every
     * code path — controller updates, API writers, or future tooling.
     */
    public function updated(MaintenanceRecord $record): void
    {
        $terminalStatuses = [MaintenanceRecord::STATUS_COMPLETED, MaintenanceRecord::STATUS_CANCELLED];

        if (! in_array($record->status, $terminalStatuses)) {
            return;
        }

        // Only act when a terminal status was just reached; repeated saves
        // of an already-terminal record do not re-trigger work.
        if (in_array($record->getOriginal('status'), $terminalStatuses)) {
            return;
        }

        // Defer until any open transaction commits so we never act on
        // data that might still roll back.
        DB::afterCommit(function () use ($record) {
            app(MaintenanceService::class)->releaseBicycleForRecord($record);
        });
    }
}
