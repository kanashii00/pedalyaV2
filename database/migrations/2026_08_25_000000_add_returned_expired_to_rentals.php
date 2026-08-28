<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rentals MODIFY status ENUM("active","pending","completed","cancelled","overdue","returned","expired") NOT NULL DEFAULT "pending"');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rentals MODIFY status ENUM("active","pending","completed","cancelled","overdue") NOT NULL DEFAULT "pending"');
        }
    }
};
