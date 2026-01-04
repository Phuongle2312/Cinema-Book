<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Bổ sung 6 bảng còn thiếu cho Cinema Booking System
     */
    public function up(): void
    {
        // 1. Bảng Loại Ghế (Seat Types) - Định nghĩa các loại ghế
        Schema::create('seat_types', function (Blueprint $table) {
            $table->id('seat_type_id');
            $table->string('name'); // VIP, Standard, Couple, Premium
            $table->string('code')->unique(); // vip, standard, couple, premium
            $table->decimal('base_extra_price', 10, 0)->default(0); // Giá phụ thu cơ bản
            $table->text('description')->nullable();
            $table->string('color_code', 7)->nullable(); // Mã màu hex cho UI: #FF5733
            $table->timestamps();
        });

        // 2. Bảng Quy Tắc Giá (Pricing Rules) - Logic giá động
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id('pricing_rule_id');
            $table->string('name'); // Giá cuối tuần, Giá lễ, Giá giờ vàng
            $table->enum('rule_type', ['time_based', 'day_based', 'seat_based', 'movie_based'])->default('time_based');

            // Điều kiện áp dụng (JSON)
            // time_based: {"start_time": "18:00", "end_time": "22:00"}
            // day_based: {"days": ["saturday", "sunday"]}
            // seat_based: {"seat_type_codes": ["vip", "couple"]}
            // movie_based: {"movie_ids": [1, 2, 3]}
            $table->json('conditions');

            // Giá trị điều chỉnh
            $table->enum('adjustment_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('adjustment_value', 10, 2); // 20000 (fixed) hoặc 15.5 (percentage)

            $table->integer('priority')->default(0); // Độ ưu tiên (số càng cao càng ưu tiên)
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            $table->timestamps();
        });

        // 3. Cập nhật bảng Seats - Thêm foreign key đến seat_types
        if (Schema::hasTable('seats')) {
            Schema::table('seats', function (Blueprint $table) {
                // Xóa cột type cũ nếu có
                if (Schema::hasColumn('seats', 'type')) {
                    $table->dropColumn('type');
                }

                // Thêm foreign key đến seat_types
                $table->foreignId('seat_type_id')->nullable()->after('number')
                    ->constrained('seat_types', 'seat_type_id')->onDelete('set null');
            });
        }

        // 5. Bảng Tickets - Chi tiết vé (thay thế booking_seats)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats', 'seat_id')->onDelete('cascade');
            $table->foreignId('showtime_id')->constrained('showtimes', 'showtime_id')->onDelete('cascade');

            $table->string('ticket_code', 30)->unique(); // Mã vé: TK20251222001

            // Giá vé chi tiết
            $table->decimal('base_price', 10, 0); // Giá gốc từ showtime
            $table->decimal('seat_extra_price', 10, 0)->default(0); // Phụ thu loại ghế
            $table->decimal('dynamic_price_adjustment', 10, 0)->default(0); // Điều chỉnh từ pricing rules
            $table->decimal('final_price', 10, 0); // Tổng giá cuối cùng

            // Thông tin giá áp dụng (JSON) - để tracking
            $table->json('applied_pricing_rules')->nullable();

            $table->enum('status', ['valid', 'used', 'cancelled', 'expired'])->default('valid');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();

            // Một ghế chỉ có 1 vé cho 1 suất chiếu
            $table->unique(['showtime_id', 'seat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('seat_locks');

        // Rollback seats table modification
        if (Schema::hasTable('seats')) {
            Schema::table('seats', function (Blueprint $table) {
                if (Schema::hasColumn('seats', 'seat_type_id')) {
                    $table->dropForeign(['seat_type_id']);
                    $table->dropColumn('seat_type_id');
                }
            });
        }

        Schema::dropIfExists('pricing_rules');
        Schema::dropIfExists('seat_types');
    }
};
