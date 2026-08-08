<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rentalId')->nullable()->constrained('rentals')->nullOnDelete();
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bicycleId')->nullable()->constrained('bicycles')->nullOnDelete();
            $table->string('paymentReference')->unique();
            $table->string('paymongoPaymentId')->unique()->nullable();
            $table->string('paymongoCheckoutUrl')->nullable();
            $table->string('paymentMethod'); // gcash, maya, grabpay, card, online_banking
            $table->decimal('amount', 12, 2); // base rental fee
            $table->decimal('convenienceFee', 12, 2)->default(0);
            $table->decimal('totalAmount', 12, 2); // amount + convenienceFee
            $table->string('currency', 3)->default('PHP');
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'expired', 'refunded', 'cancelled'])->default('pending');
            $table->json('paymentDetails')->nullable(); // PayMongo response data
            $table->json('billingInfo')->nullable(); // customer billing info
            $table->timestamp('paidAt')->nullable();
            $table->timestamp('expiredAt')->nullable();
            $table->text('failureReason')->nullable();
            $table->string('webhookSignature')->nullable(); // for verification
            $table->timestamps();
            $table->softDeletes();

            $table->index(['userId', 'status']);
            $table->index(['rentalId']);
            $table->index(['paymongoPaymentId']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};