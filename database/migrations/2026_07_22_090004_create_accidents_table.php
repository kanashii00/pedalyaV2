<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->enum('type', ['accident', 'geofence_breach', 'geofence_alert', 'impact_detected']);
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical'])->default('minor');
            $table->text('description')->nullable();
            $table->json('gpsLocation')->nullable();
            $table->json('accelerometerData')->nullable();
            $table->decimal('impactForce', 10, 2)->nullable();
            $table->string('imageUrl')->nullable();
            $table->boolean('acknowledged')->default(false);
            $table->boolean('alertSent')->default(false);
            $table->string('reportedBy')->nullable();
            $table->string('status')->default('open');
            $table->decimal('breachDistance', 10, 2)->nullable();
            $table->string('breachDirection')->nullable();
            $table->text('actionTaken')->nullable();
            $table->string('warningLevel')->nullable();
            $table->decimal('distanceFromBoundary', 10, 2)->nullable();
            $table->json('location')->nullable();
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('type');
            $table->index('severity');
            $table->index('status');
            $table->index('acknowledged');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accidents');
    }
};
