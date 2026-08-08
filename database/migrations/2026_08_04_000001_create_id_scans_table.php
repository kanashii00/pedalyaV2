<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->nullable()->constrained('users')->nullOnDelete();
            $table->string('documentType', 50)->default('other')->index();
            $table->string('idNumberHash', 64)->index();
            $table->text('fullName')->nullable();
            $table->text('idNumber')->nullable();
            $table->string('dateOfBirth', 50)->nullable();
            $table->string('expirationDate', 50)->nullable();
            $table->text('address')->nullable();
            $table->text('extractedData')->nullable();
            $table->longText('rawOcrText')->nullable();
            $table->string('frontImagePath')->nullable();
            $table->string('backImagePath')->nullable();
            $table->string('frontImageMime', 50)->nullable();
            $table->string('backImageMime', 50)->nullable();
            $table->decimal('ocrConfidence', 5, 2)->nullable();
            $table->decimal('qualityScore', 5, 2)->nullable();
            $table->decimal('blurScore', 5, 2)->nullable();
            $table->decimal('glareScore', 5, 2)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('reviewNotes')->nullable();
            $table->string('rejectionReason')->nullable();
            $table->foreignId('reviewedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewedAt')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_scans');
    }
};
