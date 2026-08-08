<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bicycleId')->constrained('bicycles')->cascadeOnDelete();
            $table->json('gps')->nullable();
            $table->json('accelerometer')->nullable();
            $table->json('battery')->nullable();
            $table->string('lockStatus')->nullable();
            $table->json('lcdTimer')->nullable();
            $table->string('rfid')->nullable();
            $table->string('deviceVersion')->nullable();
            $table->integer('uptime')->nullable();
            $table->string('command')->nullable();
            $table->foreignId('commandIssuedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('commandIssuedAt')->nullable();
            $table->enum('type', ['heartbeat', 'command', 'lock_command'])->default('heartbeat');
            $table->timestamp('eventTimestamp')->nullable();
            $table->string('status')->nullable();
            $table->json('params')->nullable();
            $table->string('issuedByName')->nullable();
            $table->timestamp('sentAt')->nullable();
            $table->timestamp('acknowledgedAt')->nullable();
            $table->timestamp('completedAt')->nullable();
            $table->text('result')->nullable();
            $table->timestamps();

            $table->index('bicycleId');
            $table->index('type');
            $table->index('eventTimestamp');
            $table->index(['bicycleId', 'eventTimestamp']);
            $table->index('command');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_status');
    }
};
