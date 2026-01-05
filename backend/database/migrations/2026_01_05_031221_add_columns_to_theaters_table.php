<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('theaters', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->text('description')->nullable()->after('phone');
            $table->string('image_url')->nullable()->after('description');
            $table->decimal('latitude', 10, 8)->nullable()->after('image_url');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->boolean('is_active')->default(true)->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theaters', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'image_url', 'latitude', 'longitude', 'is_active']);
        });
    }
};
