<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->string('shapeType')->default('circle')->after('radius')->comment('circle, oval_h, oval_v, rectangle, polygon');
            $table->decimal('width', 10, 2)->nullable()->after('shapeType')->comment('Meters (oval width / rectangle width)');
            $table->decimal('height', 10, 2)->nullable()->after('width')->comment('Meters (oval height / rectangle height)');
            $table->decimal('rotation', 10, 2)->nullable()->after('height')->comment('Degrees clockwise from north (rectangle)');
            $table->json('points')->nullable()->after('rotation')->comment('Polygon vertices [{lat,lng}] when shapeType=polygon');
        });
    }

    public function down(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->dropColumn(['rotation', 'height', 'width', 'shapeType', 'points']);
        });
    }
};
