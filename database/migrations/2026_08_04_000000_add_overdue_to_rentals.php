<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->boolean('isOverdue')->default(false)->after('paymentStatus');
            $table->timestamp('overdueAt')->nullable()->after('isOverdue');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rentals MODIFY status ENUM("active","pending","completed","cancelled","overdue") NOT NULL DEFAULT "pending"');
        }
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['isOverdue', 'overdueAt']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rentals MODIFY status ENUM("active","pending","completed","cancelled") NOT NULL DEFAULT "pending"');
        }
    }
};
