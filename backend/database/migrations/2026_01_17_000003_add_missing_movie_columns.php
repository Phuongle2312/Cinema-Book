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
        Schema::table('movies', function (Blueprint $table) {
            // Add age_rating if not exists
            if (!Schema::hasColumn('movies', 'age_rating')) {
                $table->string('age_rating', 10)->nullable()->after('release_date')->comment('P, K, C13, C16, C18');
            }
            // Add synopsis if not exists
            if (!Schema::hasColumn('movies', 'synopsis')) {
                $table->longText('synopsis')->nullable()->after('description')->comment('Full plot synopsis');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['age_rating', 'synopsis']);
        });
    }
};
