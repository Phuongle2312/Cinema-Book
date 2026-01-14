<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove reviews table and create wishlists
        Schema::dropIfExists('reviews');

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'movie_id']);
        });

        // 2. Modify users table: Remove avatar, add status/level for history tracking
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            // level: 1: free, 2: bought but not watched, 3: watched and reviewed
            $table->tinyInteger('user_level')->default(1)->comment('1: Free, 2: Bought, 3: Watched');
        });

        // 3. Create verify_payments table
        Schema::create('verify_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_code')->unique();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 4. Update promotions to offers (system-wide)
        // We will make 'code' nullable for system-wide offers
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('code')->nullable()->change();
            $table->boolean('is_system_wide')->default(false);
        });

        // Rename table might be better but let's keep it promotions for now to avoid breaking too many things,
        // OR rename it to offers. The user said "bỏ voucher mà là offers cho toàn hệ thống".
        // Let's rename it to offers.
        Schema::rename('promotions', 'offers');

        // 5. Update movies table: Add content if missing (description already exists, adding content for more detail)
        Schema::table('movies', function (Blueprint $table) {
            if (! Schema::hasColumn('movies', 'content')) {
                $table->longText('content')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::rename('offers', 'promotions');
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
            $table->dropColumn('is_system_wide');
        });
        Schema::dropIfExists('verify_payments');
        Schema::table('users', function (Blueprint $table) {
            $table->text('avatar')->nullable();
            $table->dropColumn('user_level');
        });
        Schema::dropIfExists('wishlists');
        // We can't easily restore reviews because we dropped it.
    }
};
