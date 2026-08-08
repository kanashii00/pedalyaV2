<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 6, 2)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->integer('batteryLevel')->nullable();
            $table->decimal('altitude', 8, 2)->nullable();
            $table->integer('satellites')->nullable();
            $table->decimal('hdop', 6, 2)->nullable();
            $table->timestamp('timestamp');
            $table->string('source')->default('gps');
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('timestamp');
            $table->index(['bicycleId', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_logs');
    }
};
