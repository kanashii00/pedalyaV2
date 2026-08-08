<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bicycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('serialNumber')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'rented', 'maintenance', 'locked', 'removed'])->default('available');
            $table->decimal('hourlyRate', 8, 2);
            $table->decimal('currentLat', 10, 7)->nullable();
            $table->decimal('currentLng', 10, 7)->nullable();
            $table->integer('batteryLevel')->default(100);
            $table->enum('lockStatus', ['locked', 'unlocked'])->default('locked');
            $table->string('qrCode')->nullable()->unique();
            $table->integer('totalRentals')->default(0);
            $table->decimal('totalDistance', 10, 2)->default(0);
            $table->enum('condition', ['good', 'needs_inspection', 'damaged'])->default('good');
            $table->timestamp('lastMaintenanceDate')->nullable();
            $table->foreignId('currentRider')->nullable()->constrained('users')->nullOnDelete();
            $table->string('currentRentalId')->nullable();
            $table->timestamp('lastGpsUpdate')->nullable();
            $table->timestamp('lastHeartbeat')->nullable();
            $table->timestamp('lastLockAction')->nullable();
            $table->string('lockActionBy')->nullable();
            $table->foreignId('addedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('removedAt')->nullable();
            $table->foreignId('removedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('lockStatus');
            $table->index('condition');
            $table->index('currentRider');
            $table->index('currentLat', 'currentLng');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bicycles');
    }
};
