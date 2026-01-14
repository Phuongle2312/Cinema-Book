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
        Schema::table('bookings', function (Blueprint $table) {
            // Tách tổng tiền thành 2 phần: ghế và combo
            $table->decimal('seats_total', 10, 0)->default(0)->after('total_seats');
            $table->decimal('combo_total', 10, 0)->default(0)->after('seats_total');

            // total_price sẽ = seats_total + combo_total
            // Không cần modify cột total_price vì đã tồn tại
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['seats_total', 'combo_total']);
        });
    }
};
