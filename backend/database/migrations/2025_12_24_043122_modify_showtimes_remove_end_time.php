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
        Schema::table('showtimes', function (Blueprint $table) {
            // Xóa cột end_time vì có thể tính tự động = start_time + movie.duration
            $table->dropColumn('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimes', function (Blueprint $table) {
            // Khôi phục lại cột end_time
            $table->dateTime('end_time')->after('start_time');
        });
    }
};
