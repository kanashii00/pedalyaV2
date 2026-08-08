<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Geofence');
            $table->decimal('centerLat', 10, 7);
            $table->decimal('centerLng', 10, 7);
            $table->decimal('radius', 10, 2)->comment('Radius in meters');
            $table->boolean('isActive')->default(true);
            $table->boolean('alertEnabled')->default(true);
            $table->timestamps();

            $table->index('isActive');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
