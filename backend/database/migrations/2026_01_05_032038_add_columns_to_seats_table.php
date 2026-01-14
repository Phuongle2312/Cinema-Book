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
        Schema::table('seats', function (Blueprint $table) {
            if (! Schema::hasColumn('seats', 'seat_code')) {
                $table->string('seat_code')->after('number');
            }
            if (! Schema::hasColumn('seats', 'seat_type')) {
                $table->string('seat_type')->default('standard')->after('seat_code');
            }
            if (! Schema::hasColumn('seats', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('seat_type');
            }

            // Drop old columns if they exist
            if (Schema::hasColumn('seats', 'seat_type_id')) {
                try {
                    $table->dropForeign(['seat_type_id']);
                } catch (\Exception $e) {
                }
                $table->dropColumn('seat_type_id');
            }
            if (Schema::hasColumn('seats', 'extra_price')) {
                $table->dropColumn('extra_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropColumn(['seat_code', 'seat_type', 'is_available']);
            $table->unsignedBigInteger('seat_type_id')->nullable();
            $table->decimal('extra_price', 10, 2)->default(0);
        });
    }
};
