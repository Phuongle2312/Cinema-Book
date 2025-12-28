<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration tổng hợp cho hệ thống Cinema Booking
     * Bao gồm 15 bảng chính được sắp xếp theo thứ tự phụ thuộc
     */
    public function up(): void
    {
        // ============================================
        // NHÓM 1: METADATA TABLES (Thể loại, Ngôn ngữ, Diễn viên)
        // ============================================
        
        // 1. Bảng Thể loại phim (Genres)
        Schema::create('genres', function (Blueprint $table) {
            $table->id('genre_id');
            $table->string('name')->unique(); // Vd: Action, Horror, Comedy
            $table->string('slug')->unique(); // Vd: action, horror, comedy
            $table->timestamps();
        });

        // 2. Bảng Ngôn ngữ (Languages)
        Schema::create('languages', function (Blueprint $table) {
            $table->id('language_id');
            $table->string('name')->unique(); // Vd: English, Vietnamese, Korean
            $table->string('code', 10)->unique(); // Vd: en, vi, ko
            $table->timestamps();
        });

        // 3. Bảng Diễn viên/Đạo diễn (Cast)
        Schema::create('cast', function (Blueprint $table) {
            $table->id('cast_id');
            $table->string('name');
            $table->enum('type', ['actor', 'director', 'both'])->default('actor');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });

        // ============================================
        // NHÓM 3: CORE TABLES (Rạp, Phòng chiếu, Phim)
        // ============================================
        
        // 4. Bảng Rạp chiếu (Theaters)
        Schema::create('theaters', function (Blueprint $table) {
            $table->id('theater_id');
            $table->string('name');
            $table->string('city'); // Dùng để lọc theo thành phố
            $table->string('address');
            $table->string('phone')->nullable();
            $table->text('facilities')->nullable(); // JSON: ["parking", "food_court", "3D"]
            $table->timestamps();
        });

        // 5. Bảng Phòng chiếu (Screens)
        Schema::create('screens', function (Blueprint $table) {
            $table->id('screen_id');
            $table->foreignId('theater_id')->constrained('theaters', 'theater_id')->onDelete('cascade');
            $table->string('name'); // Vd: Phòng 01, IMAX
            $table->integer('total_seats')->default(0); // Tổng số ghế
            $table->enum('screen_type', ['standard', 'vip', 'imax', '4dx'])->default('standard');
            $table->timestamps();
        });

        // 6. Bảng Phim (Movies)
        Schema::create('movies', function (Blueprint $table) {
            $table->id('movie_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('duration'); // Số phút
            $table->date('release_date');
            $table->string('poster_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->decimal('rating', 3, 1)->default(0); // Vd: 8.5
            $table->enum('status', ['coming_soon', 'now_showing', 'ended'])->default('coming_soon');
            $table->timestamps();
        });

        // ============================================
        // NHÓM 4: PIVOT TABLES (Many-to-Many)
        // ============================================
        
        // 7. Bảng Phim-Thể loại (Movie-Genre)
        Schema::create('movie_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            $table->foreignId('genre_id')->constrained('genres', 'genre_id')->onDelete('cascade');
            $table->timestamps();
            
            // Đảm bảo không trùng lặp
            $table->unique(['movie_id', 'genre_id']);
        });

        // 8. Bảng Phim-Ngôn ngữ (Movie-Language)
        Schema::create('movie_language', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            $table->foreignId('language_id')->constrained('languages', 'language_id')->onDelete('cascade');
            $table->enum('type', ['original', 'subtitle', 'dubbed'])->default('subtitle');
            $table->timestamps();
            
            $table->unique(['movie_id', 'language_id', 'type']);
        });

        // 9. Bảng Phim-Diễn viên (Movie-Cast)
        Schema::create('movie_cast', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            $table->foreignId('cast_id')->constrained('cast', 'cast_id')->onDelete('cascade');
            $table->enum('role', ['actor', 'director'])->default('actor');
            $table->string('character_name')->nullable(); // Tên nhân vật (nếu là diễn viên)
            $table->timestamps();
        });

        // ============================================
        // NHÓM 5: BOOKING SYSTEM (Lịch chiếu, Ghế)
        // ============================================
        
        // 10. Bảng Lịch chiếu (Showtimes)
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id('showtime_id');
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            $table->foreignId('screen_id')->constrained('screens', 'screen_id')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time'); // Tự động tính = start_time + duration
            
            // Giá gốc do Admin set (80k ngày thường, 100k cuối tuần)
            $table->decimal('base_price', 10, 0);
            
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
            
            // Không cho trùng lịch chiếu cùng phòng cùng giờ
            $table->unique(['screen_id', 'start_time']);
        });

        // 11. Bảng Ghế ngồi (Seats)
        Schema::create('seats', function (Blueprint $table) {
            $table->id('seat_id');
            $table->foreignId('screen_id')->constrained('screens', 'screen_id')->onDelete('cascade');
            $table->string('row'); // Vd: A, B, C
            $table->integer('number'); // Vd: 1, 2, 3
            $table->enum('type', ['standard', 'vip', 'couple'])->default('standard');
            
            // Giá phụ thu theo loại ghế (VIP +20k, Couple +30k)
            $table->decimal('extra_price', 10, 0)->default(0);
            
            $table->timestamps();
            
            // Mỗi phòng không có ghế trùng
            $table->unique(['screen_id', 'row', 'number']);
        });

        // ============================================
        // NHÓM 6: TRANSACTION TABLES (Đặt vé, Thanh toán)
        // ============================================
        
        // 12. Bảng Đặt vé (Bookings)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('showtime_id')->constrained('showtimes', 'showtime_id')->onDelete('cascade');
            
            $table->string('booking_code', 20)->unique(); // Mã đặt vé: BK20251222001
            $table->integer('total_seats'); // Số ghế đã đặt
            $table->decimal('total_price', 10, 0); // Tổng tiền
            
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable(); // Hết hạn sau 5-6 phút nếu chưa thanh toán
            
            $table->timestamps();
        });

        // 13. Bảng Chi tiết ghế đã đặt (Booking Seats)
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

        // 14. Bảng Khóa ghế tạm thời (Seat Locks) - 5-6 phút
        Schema::create('seat_locks', function (Blueprint $table) {
            $table->id('lock_id');
            $table->foreignId('seat_id')->constrained('seats', 'seat_id')->onDelete('cascade');
            $table->foreignId('showtime_id')->constrained('showtimes', 'showtime_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->timestamp('locked_at'); // Thời gian khóa
            $table->timestamp('expires_at'); // Hết hạn sau 5-6 phút
            
            $table->timestamps();
            
            // Một ghế chỉ bị khóa 1 lần cho 1 suất chiếu
            $table->unique(['showtime_id', 'seat_id']);
        });

        // 15. Bảng Giao dịch thanh toán (Transactions)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('transaction_code', 30)->unique(); // Mã giao dịch
            $table->decimal('amount', 10, 0); // Số tiền
            $table->enum('payment_method', ['cash', 'credit_card', 'momo', 'zalopay', 'vnpay'])->default('cash');
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            
            $table->text('payment_details')->nullable(); // JSON: thông tin từ cổng thanh toán
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
        });

        // 16. Bảng Đánh giá phim (Reviews)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            
            $table->integer('rating'); // 1-10 hoặc 1-5
            $table->text('comment')->nullable();
            
            $table->timestamps();
            
            // Mỗi user chỉ đánh giá 1 lần cho 1 phim
            $table->unique(['user_id', 'movie_id']);
        });
    }

    /**
     * Reverse the migrations.
     * Xóa theo thứ tự ngược lại để tránh lỗi foreign key
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('seat_locks');
        Schema::dropIfExists('booking_seats');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('showtimes');
        Schema::dropIfExists('movie_cast');
        Schema::dropIfExists('movie_language');
        Schema::dropIfExists('movie_genre');
        Schema::dropIfExists('movies');
        Schema::dropIfExists('screens');
        Schema::dropIfExists('theaters');
        Schema::dropIfExists('cast');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('genres');
    }
};
