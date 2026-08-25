<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rentals MODIFY status ENUM("active","pending","completed","cancelled","overdue","returned","expired") NOT NULL DEFAULT "pending"');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rentals MODIFY status ENUM("active","pending","completed","cancelled","overdue") NOT NULL DEFAULT "pending"');
    }
};
