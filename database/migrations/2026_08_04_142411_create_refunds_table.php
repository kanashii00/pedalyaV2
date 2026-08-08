<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paymentId')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete(); // admin who initiated
            $table->string('refundReference')->unique();
            $table->string('paymongoRefundId')->unique()->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->enum('reason', ['customer_request', 'duplicate_payment', 'fraudulent', 'service_not_provided', 'other'])->default('customer_request');
            $table->text('reasonDetails')->nullable();
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'cancelled'])->default('pending');
            $table->json('paymongoResponse')->nullable();
            $table->timestamp('processedAt')->nullable();
            $table->text('failureReason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['paymentId']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};