<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('userId')->nullable()->constrained('users')->nullOnDelete();
            $table->json('details')->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();

            $table->index('action');
            $table->index('userId');
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
