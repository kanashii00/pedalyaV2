<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('rentalId')->unique();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->string('bicycleName')->nullable();
            $table->string('bicycleSerial')->nullable();
            $table->foreignId('riderId')->constrained('users')->cascadeOnDelete();
            $table->string('riderName')->nullable();
            $table->string('riderEmail')->nullable();
            $table->enum('status', ['active', 'pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('startTime')->nullable();
            $table->timestamp('endTime')->nullable();
            $table->json('startLocation')->nullable();
            $table->json('endLocation')->nullable();
            $table->decimal('ratePerHour', 8, 2);
            $table->decimal('totalFee', 10, 2)->default(0);
            $table->integer('durationMinutes')->default(0);
            $table->string('durationFormatted')->nullable();
            $table->integer('chargedHours')->default(0);
            $table->decimal('totalDistance', 10, 2)->default(0);
            $table->string('paymentStatus')->default('pending');
            $table->string('paymentMethod')->nullable();
            $table->string('paymentReference')->nullable();
            $table->text('notes')->nullable();
            $table->string('cancelledBy')->nullable();
            $table->text('cancelReason')->nullable();
            $table->foreignId('approvedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approvedAt')->nullable();
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('riderId');
            $table->index('status');
            $table->index('paymentStatus');
            $table->index('startTime');
            $table->index('endTime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
