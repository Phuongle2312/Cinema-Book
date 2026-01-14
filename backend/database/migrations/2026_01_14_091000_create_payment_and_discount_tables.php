<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration Plan Phase 3: New Features
     * 
     * Creates:
     * 1. payment_verifications - Admin manual payment verification
     * 2. movie_discounts - Admin-controlled movie discounts
     */
    public function up(): void
    {
        // 1. Create payment_verifications table
        if (!Schema::hasTable('payment_verifications')) {
            Schema::create('payment_verifications', function (Blueprint $table) {
                $table->id('verification_id');
                $table->unsignedBigInteger('booking_id');
                $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
                
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                
                // Admin who verified
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
                
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->string('payment_proof')->nullable()->comment('Image/screenshot proof');
                $table->string('payment_method', 50)->default('bank_transfer');
                $table->decimal('amount', 12, 0)->default(0);
                $table->text('customer_note')->nullable()->comment('Note from customer');
                $table->text('admin_note')->nullable()->comment('Note from admin');
                $table->timestamp('submitted_at')->useCurrent();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('status');
                $table->index(['user_id', 'status']);
                $table->index('submitted_at');
            });
        }

        // 2. Create movie_discounts table
        if (!Schema::hasTable('movie_discounts')) {
            Schema::create('movie_discounts', function (Blueprint $table) {
                $table->id('discount_id');
                $table->unsignedBigInteger('movie_id');
                $table->foreign('movie_id')->references('movie_id')->on('movies')->cascadeOnDelete();
                
                $table->string('name', 100)->comment('Discount campaign name');
                $table->text('description')->nullable();
                
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 10, 2)->comment('10 = 10% or 10000 = 10,000 VND');
                $table->decimal('max_discount', 12, 0)->nullable()->comment('Max VND discount for percentage type');
                
                $table->date('start_date');
                $table->date('end_date');
                
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                
                $table->timestamps();

                // Indexes
                $table->index('is_active');
                $table->index(['movie_id', 'is_active']);
                $table->index(['start_date', 'end_date']);
            });
        }

        // 3. Add confirmed_at to bookings if not exists (for tracking when payment verified)
        if (!Schema::hasColumn('bookings', 'confirmed_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->timestamp('confirmed_at')->nullable()->after('status');
            });
        }

        // 4. Add verified_by to bookings (admin who confirmed)
        if (!Schema::hasColumn('bookings', 'verified_by')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('confirmed_at');
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        if (Schema::hasColumn('bookings', 'verified_by')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn('verified_by');
            });
        }

        if (Schema::hasColumn('bookings', 'confirmed_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('confirmed_at');
            });
        }

        Schema::dropIfExists('movie_discounts');
        Schema::dropIfExists('payment_verifications');
    }
};
