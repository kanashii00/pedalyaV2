<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();

            $table->string('google_id')->nullable()->unique()
                ->after('email_verified_at');
            $table->string('avatar')->nullable()
                ->after('google_id');
            $table->string('oauth_provider')->nullable()
                ->after('avatar');
            $table->timestamp('last_login_at')->nullable()
                ->after('oauth_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'avatar',
                'oauth_provider',
                'last_login_at',
            ]);
        });
    }
};
