<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'rider'])->default('rider');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('verified')->default(false);
            $table->boolean('idUploaded')->default(false);
            $table->json('idVerification')->nullable();
            $table->string('profilePicture')->nullable();
            $table->integer('totalRentals')->default(0);
            $table->decimal('totalSpent', 10, 2)->default(0);
            $table->string('phoneNumber')->nullable();
            $table->text('address')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('role');
            $table->index('status');
            $table->index('verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
