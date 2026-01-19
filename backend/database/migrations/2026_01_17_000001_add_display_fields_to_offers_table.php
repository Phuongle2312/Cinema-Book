<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            // Add display fields with nullable for backward compatibility
            $table->string('title')->nullable()->after('id');
            $table->string('image_url')->nullable()->after('description');
            $table->string('tag')->nullable()->after('image_url')->comment('e.g. Member Only, Discount');
            $table->string('type')->default('offer')->after('tag')->comment('offer or event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['title', 'image_url', 'tag', 'type']);
        });
    }
};
