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
        Schema::create('booking_combos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
            $table->foreignId('combo_id')->constrained('combos', 'combo_id')->onDelete('cascade');
            
            $table->integer('quantity')->default(1); // Số lượng combo
            $table->decimal('unit_price', 10, 0); // Giá tại thời điểm đặt (lưu lại để tracking)
            $table->decimal('total_price', 10, 0); // = quantity * unit_price
            
            $table->timestamps();
            
            // Index để tăng tốc query
            $table->index(['booking_id', 'combo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_combos');
    }
};
