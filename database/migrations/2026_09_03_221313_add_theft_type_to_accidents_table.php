<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow theft / boundary-breach alerts to be recorded with the canonical
     * 'theft' type so the Theft Detection module, dashboard counters and
     * incident reports all share one source of truth.
     */
    public function up(): void
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->enum('type', ['accident', 'geofence_breach', 'geofence_alert', 'impact_detected', 'theft'])
                ->default('accident')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->enum('type', ['accident', 'geofence_breach', 'geofence_alert', 'impact_detected'])
                ->default('accident')
                ->change();
        });
    }
};
