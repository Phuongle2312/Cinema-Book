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
            if (! Schema::hasColumn('showtimes', 'show_date')) {
                $table->date('show_date')->nullable()->after('room_id');
            }
            if (! Schema::hasColumn('showtimes', 'show_time')) {
                $table->string('show_time')->nullable()->after('show_date');
            }
            if (! Schema::hasColumn('showtimes', 'vip_price')) {
                $table->decimal('vip_price', 10, 2)->default(0)->after('base_price');
            }
            if (! Schema::hasColumn('showtimes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('vip_price');
            }
            if (! Schema::hasColumn('showtimes', 'available_seats')) {
                $table->integer('available_seats')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropColumn(['show_date', 'show_time', 'vip_price', 'is_active', 'available_seats']);
        });
    }
};
