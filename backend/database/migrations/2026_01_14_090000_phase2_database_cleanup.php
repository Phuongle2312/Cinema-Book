<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration Plan Phase 2: Database Cleanup
     * 
     * Changes:
     * 1. Drop tables: cast, movie_cast, reviews, promotions, genres, languages, movie_genre, movie_language
     * 2. Remove avatar column from users table
     * 3. Add actor, director columns to movies table
     * 4. Drop jobs, failed_jobs, job_batches tables (system tables không cần thiết)
     */
    public function up(): void
    {
        // 1. Add actor and director to movies FIRST (before dropping related tables)
        if (!Schema::hasColumn('movies', 'actor')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->string('actor', 500)->nullable()->after('description');
            });
        }

        if (!Schema::hasColumn('movies', 'director')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->string('director', 255)->nullable()->after('actor');
            });
        }

        // 2. Drop movie_ratings view if exists (depends on reviews)
        DB::statement('DROP VIEW IF EXISTS movie_ratings');

        // 3. Drop foreign key constrained tables first (order matters!)
        
        // Drop movie_cast (depends on cast and movies)
        Schema::dropIfExists('movie_cast');
        
        // Drop movie_genre (depends on genres and movies)
        Schema::dropIfExists('movie_genre');
        
        // Drop movie_language (depends on languages and movies)
        Schema::dropIfExists('movie_language');
        
        // Drop reviews (depends on users, movies, bookings)
        Schema::dropIfExists('reviews');
        
        // 4. Now drop parent tables
        Schema::dropIfExists('cast');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('promotions');
        
        // 5. Drop system tables (không cần cho production)
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');

        // 6. Remove avatar from users
        if (Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate system tables
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        // Recreate promotions table
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_purchase_amount', 10, 2)->nullable();
                $table->decimal('max_discount_amount', 10, 2)->nullable();
                $table->datetime('valid_from')->nullable();
                $table->datetime('valid_to')->nullable();
                $table->integer('max_uses')->nullable();
                $table->integer('current_uses')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Recreate reviews table
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id('review_id');
                $table->foreignId('user_id')->constrained('users', 'id')->cascadeOnDelete();
                $table->unsignedBigInteger('movie_id');
                $table->foreign('movie_id')->references('movie_id')->on('movies')->cascadeOnDelete();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->boolean('is_verified_purchase')->default(false);
                $table->integer('rating');
                $table->text('comment')->nullable();
                $table->timestamps();
            });
        }

        // Recreate cast table
        if (!Schema::hasTable('cast')) {
            Schema::create('cast', function (Blueprint $table) {
                $table->id('cast_id');
                $table->string('name');
                $table->enum('type', ['actor', 'director', 'both'])->default('actor');
                $table->string('avatar')->nullable();
                $table->text('bio')->nullable();
                $table->timestamps();
            });
        }

        // Recreate genres table
        if (!Schema::hasTable('genres')) {
            Schema::create('genres', function (Blueprint $table) {
                $table->id('genre_id');
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        // Recreate languages table
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id('language_id');
                $table->string('name')->unique();
                $table->string('code', 10)->unique();
                $table->timestamps();
            });
        }

        // Add back avatar to users
        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('avatar')->nullable()->after('provider_id');
            });
        }

        // Remove actor and director from movies
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'actor')) {
                $table->dropColumn('actor');
            }
            if (Schema::hasColumn('movies', 'director')) {
                $table->dropColumn('director');
            }
        });
    }
};
