<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL to avoid dependency on doctrine/dbal
        DB::statement('ALTER TABLE movies MODIFY COLUMN poster_url LONGTEXT NULL');
        DB::statement('ALTER TABLE movies MODIFY COLUMN banner_url LONGTEXT NULL');
        DB::statement('ALTER TABLE movies MODIFY COLUMN trailer_url LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE movies MODIFY COLUMN poster_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE movies MODIFY COLUMN banner_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE movies MODIFY COLUMN trailer_url VARCHAR(255) NULL');
    }
};
