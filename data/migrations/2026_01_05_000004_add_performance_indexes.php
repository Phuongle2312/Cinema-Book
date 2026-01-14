<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm indexes để tối ưu performance cho các query thường dùng
     */
    public function up(): void
    {
        // Use raw SQL with IF NOT EXISTS to avoid duplicate index errors
        
        // 1. Indexes cho bảng movies
        DB::statement('CREATE INDEX IF NOT EXISTS movies_status_index ON movies(status)');
        DB::statement('CREATE INDEX IF NOT EXISTS movies_release_date_index ON movies(release_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS movies_status_release_date_index ON movies(status, release_date)');

        // 2. Indexes cho bảng showtimes  
        DB::statement('CREATE INDEX IF NOT EXISTS showtimes_start_time_index ON showtimes(start_time)');
        DB::statement('CREATE INDEX IF NOT EXISTS showtimes_status_index ON showtimes(status)');
        DB::statement('CREATE INDEX IF NOT EXISTS showtimes_movie_id_start_time_index ON showtimes(movie_id, start_time)');

        // 3. Indexes cho bảng bookings
        DB::statement('CREATE INDEX IF NOT EXISTS bookings_booking_code_index ON bookings(booking_code)');
        DB::statement('CREATE INDEX IF NOT EXISTS bookings_status_index ON bookings(status)');
        DB::statement('CREATE INDEX IF NOT EXISTS bookings_user_id_status_index ON bookings(user_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS bookings_created_at_index ON bookings(created_at)');

        // 4. Indexes cho bảng transactions
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_transaction_code_index ON transactions(transaction_code)');
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_status_index ON transactions(status)');
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_payment_method_index ON transactions(payment_method)');
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_user_id_status_index ON transactions(user_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_paid_at_index ON transactions(paid_at)');

        // 5. Indexes cho bảng reviews
        DB::statement('CREATE INDEX IF NOT EXISTS reviews_movie_id_index ON reviews(movie_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS reviews_rating_index ON reviews(rating)');
        DB::statement('CREATE INDEX IF NOT EXISTS reviews_movie_id_created_at_index ON reviews(movie_id, created_at)');

        // 6. Indexes cho bảng seat_locks
        DB::statement('CREATE INDEX IF NOT EXISTS seat_locks_expires_at_index ON seat_locks(expires_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS seat_locks_showtime_id_expires_at_index ON seat_locks(showtime_id, expires_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all indexes in reverse order
        Schema::table('seat_locks', function (Blueprint $table) {
            $table->dropIndex(['showtime_id', 'expires_at']);
            $table->dropIndex(['expires_at']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['movie_id', 'created_at']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['movie_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['status']);
            $table->dropIndex(['transaction_code']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['status']);
            $table->dropIndex(['booking_code']);
        });

        Schema::table('showtimes', function (Blueprint $table) {
            $table->dropIndex(['screen_id', 'start_time']);
            $table->dropIndex(['movie_id', 'start_time']);
            $table->dropIndex(['status']);
            $table->dropIndex(['start_time']);
        });

        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['status', 'release_date']);
            $table->dropIndex(['release_date']);
            $table->dropIndex(['status']);
        });
    }

    /**
     * Helper method to check if index exists
     */
    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return isset($indexes[$index]);
    }
};
