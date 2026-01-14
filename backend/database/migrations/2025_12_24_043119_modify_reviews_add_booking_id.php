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
        Schema::table('reviews', function (Blueprint $table) {
            // Thêm booking_id để đảm bảo user đã xem phim mới review được
            $table->foreignId('booking_id')
                ->nullable()
                ->after('movie_id')
                ->constrained('bookings', 'booking_id')
                ->onDelete('cascade');

            // Thêm cờ để đánh dấu review từ người đã mua vé
            $table->boolean('is_verified_purchase')->default(false)->after('booking_id');

            // Thêm unique constraint: mỗi booking chỉ có 1 review
            $table->unique('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Xóa unique constraint
            $table->dropUnique(['booking_id']);

            // Xóa các cột đã thêm
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['booking_id', 'is_verified_purchase']);

            // Khôi phục lại unique constraint cũ
            $table->unique(['user_id', 'movie_id']);
        });
    }
};
