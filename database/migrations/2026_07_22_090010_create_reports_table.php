<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('reportId')->unique();
            $table->string('type');
            $table->string('format')->default('pdf');
            $table->string('status')->default('pending');
            $table->json('filters')->nullable();
            $table->json('summary')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('generatedAt')->nullable();
            $table->foreignId('generatedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->string('downloadUrl')->nullable();
            $table->string('fileName')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('generatedBy');
            $table->index('generatedAt');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
