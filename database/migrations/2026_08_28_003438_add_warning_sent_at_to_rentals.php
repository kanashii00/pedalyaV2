<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->timestamp('warningSentAt')->nullable()->after('overdueAt');
        });

        // Backfill endTime for active/pending rentals created before the
        // countdown feature (endTime = startTime + selected duration) so the
        // server-side countdown and overdue checks work for existing rentals too.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('rentals')
                ->whereNull('endTime')
                ->whereNotNull('startTime')
                ->whereIn('status', ['active', 'pending', 'overdue'])
                ->whereNotNull('durationMinutes')
                ->update([
                    'endTime' => DB::raw('DATE_ADD(startTime, INTERVAL durationMinutes MINUTE)'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('warningSentAt');
        });
    }
};
