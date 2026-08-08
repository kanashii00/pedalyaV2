<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->boolean('read')->default(false);
            $table->foreignId('bicycleId')->nullable()->constrained('bicycles')->nullOnDelete();
            $table->string('rentalId')->nullable();
            $table->string('incidentId')->nullable();
            $table->timestamp('readAt')->nullable();
            $table->string('sentBy')->nullable();
            $table->timestamps();

            $table->index('userId');
            $table->index('read');
            $table->index('type');
            $table->index('bicycleId');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
