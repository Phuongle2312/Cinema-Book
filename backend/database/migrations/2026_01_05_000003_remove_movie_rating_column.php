<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xóa cột rating từ movies và tạo database view để tính động
     */
    public function up(): void
    {
        // Skipped to prevent errors and keep rating column
        // Schema::table('movies', function (Blueprint $table) {
        //     $table->dropColumn('rating');
        // });
        // DB::statement('CREATE OR REPLACE VIEW ...');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skipped
    }
};
