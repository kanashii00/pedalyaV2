<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->decimal('warningThreshold', 10, 2)->nullable()->after('alertEnabled')->comment('Distance in meters from boundary that triggers a warning');
        });
    }

    public function down(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->dropColumn('warningThreshold');
        });
    }
};
