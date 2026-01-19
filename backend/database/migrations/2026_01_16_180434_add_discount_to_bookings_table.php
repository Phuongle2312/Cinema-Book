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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('total_price');
            }
            if (!Schema::hasColumn('bookings', 'offer_id')) {
                $table->unsignedBigInteger('offer_id')->nullable()->after('discount_amount');
            }

            // Add FK separately to avoid issues if it already exists or if we need to catch error
            // checks for FK existence is harder, but we can try-catch or just rely on 'offer_id' existing
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Re-attempt FK with correct reference 'id'
            // We give it a specific name to avoid duplicates if possible, or Laravel handles it.
            // But if it exists, it might fail. Use try-catch block in raw PHP if needed?
            // No, standard migrations don't do try-catch.
            // We'll just define it. If it fails saying "Constraint already exists", we are good.
            // But valid FK requires valid data. 'offer_id' is nullable, so current data is likely NULL or 0? 
            // Default was NULL in my code.
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['offer_id']);
            $table->dropColumn(['discount_amount', 'offer_id']);
        });
    }
};
