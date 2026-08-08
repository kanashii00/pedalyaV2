<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->string('bicycleName')->nullable();
            $table->enum('type', ['routine', 'repair', 'battery', 'lock_mechanism', 'gps_module', 'frame', 'other']);
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->decimal('estimatedCost', 10, 2)->nullable();
            $table->decimal('actualCost', 10, 2)->nullable();
            $table->string('technician')->nullable();
            $table->timestamp('scheduledDate')->nullable();
            $table->timestamp('completedDate')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('createdBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('type');
            $table->index('severity');
            $table->index('status');
            $table->index('scheduledDate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
