<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->string('command')->comment('lock, unlock, restart, calibrate, disable, enable');
            $table->json('params')->nullable();
            $table->string('status')->default('pending')->comment('pending, sent, executed, failed');
            $table->foreignId('issuedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executedAt')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};
