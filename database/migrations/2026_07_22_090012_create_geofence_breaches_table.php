<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geofence_breaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->foreignId('geofenceId')->nullable()->constrained('geofences')->nullOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('distance', 10, 2)->comment('Distance from geofence center in meters');
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('resolvedAt')->nullable();
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('acknowledged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geofence_breaches');
    }
};
