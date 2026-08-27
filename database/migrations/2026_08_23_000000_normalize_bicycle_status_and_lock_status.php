<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separates bicycle operational status from smart lock state.
 *
 * Status now only represents the rental/operational state
 * (available, rented, maintenance, removed). The physical smart-lock
 * state lives exclusively in lockStatus (locked / unlocked).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Bikes stuck in the legacy "locked" status become available again;
        // their physical lock state stays recorded in lockStatus.
        DB::table('bicycles')->where('status', 'locked')->update(['status' => 'available']);

        // Bicycles with an ongoing (authorized) rental are Rented and Unlocked.
        DB::table('bicycles')
            ->whereIn('id', function ($query) {
                $query->select('bicycleId')
                    ->from('rentals')
                    ->whereIn('status', ['active', 'overdue']);
            })
            ->where('status', '!=', 'removed')
            ->update(['status' => 'rented', 'lockStatus' => 'unlocked']);

        // Every secured, not-in-use bicycle is Locked.
        DB::table('bicycles')
            ->whereNotIn('status', ['rented'])
            ->update(['lockStatus' => 'locked']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE bicycles MODIFY status ENUM('available','rented','maintenance','removed') NOT NULL DEFAULT 'available'"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE bicycles MODIFY status ENUM('available','rented','maintenance','locked','removed') NOT NULL DEFAULT 'available'"
            );
        }
    }
};
