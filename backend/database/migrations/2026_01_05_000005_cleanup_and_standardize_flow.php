<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cleanup redundant tables and standardize flow constraints.
     */
    public function up(): void
    {
        // 1. Migrate data from booking_seats to booking_details if any unique data exists
        // In this case, both tables usually exist because of previous inconsistent migrations.
        // booking_details (formerly tickets) is more comprehensive.
        
        // 2. Drop legacy screens table (renamed to rooms previously)
        Schema::dropIfExists('screens');

        // 3. Drop redundant booking_seats table
        Schema::dropIfExists('booking_seats');

        // 4. Strengthen booking_details
        if (Schema::hasTable('booking_details')) {
            Schema::table('booking_details', function (Blueprint $table) {
                // Ensure foreign keys have CASCADE DELETE for "Standard Flow"
                // Laravel convention for foreign key name: booking_details_booking_id_foreign
                
                // 1. Booking relationship
                try {
                    $table->dropForeign('booking_details_booking_id_foreign');
                } catch (\Exception $e) {}
                
                $table->foreign('booking_id')
                    ->references('booking_id')
                    ->on('bookings')
                    ->onDelete('cascade');

                // 2. Seat relationship
                try {
                    $table->dropForeign('booking_details_seat_id_foreign');
                } catch (\Exception $e) {}
                
                $table->foreign('seat_id')
                    ->references('seat_id')
                    ->on('seats')
                    ->onDelete('cascade');
            });
        }

        // 5. Ensure transactions have CASCADE DELETE from bookings
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                try {
                    $table->dropForeign('transactions_booking_id_foreign');
                } catch (\Exception $e) {}
                
                $table->foreign('booking_id')
                    ->references('booking_id')
                    ->on('bookings')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-creating dropped tables would be complex due to data loss, 
        // but for structural integrity:
        Schema::create('booking_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats', 'seat_id')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }
};
