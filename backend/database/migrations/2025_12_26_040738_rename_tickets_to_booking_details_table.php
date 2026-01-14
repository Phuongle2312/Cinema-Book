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
        // Kiểm tra xem bảng tickets có tồn tại không
        if (Schema::hasTable('tickets')) {
            // Đổi tên bảng tickets thành booking_details
            Schema::rename('tickets', 'booking_details');

            // Đổi tên primary key nếu cần
            Schema::table('booking_details', function (Blueprint $table) {
                if (Schema::hasColumn('booking_details', 'ticket_id')) {
                    $table->renameColumn('ticket_id', 'detail_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('booking_details')) {
            // Đổi lại primary key
            Schema::table('booking_details', function (Blueprint $table) {
                if (Schema::hasColumn('booking_details', 'detail_id')) {
                    $table->renameColumn('detail_id', 'ticket_id');
                }
            });

            // Đổi lại tên bảng
            Schema::rename('booking_details', 'tickets');
        }
    }
};
