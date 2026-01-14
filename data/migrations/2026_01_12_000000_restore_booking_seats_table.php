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
        if (!Schema::hasTable('booking_seats')) {
            Schema::create('booking_seats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
                $table->foreignId('seat_id')->constrained('seats', 'seat_id')->onDelete('cascade');
                $table->foreignId('showtime_id')->constrained('showtimes', 'showtime_id')->onDelete('cascade');

                $table->decimal('price', 10, 0); // Giá ghế này = base_price + extra_price

                $table->timestamps();

                // Một ghế chỉ được đặt 1 lần cho 1 suất chiếu
                $table->unique(['showtime_id', 'seat_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_seats');
    }
};
