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
        // 1. Tạo bảng hashtags
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id('hashtag_id');
            $table->string('name')->unique();
            $table->string('type'); // 'genre' hoặc 'language'
            $table->timestamps();
        });

        // 2. Tạo bảng pivot movie_hashtag
        Schema::create('movie_hashtag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies', 'movie_id')->onDelete('cascade');
            $table->foreignId('hashtag_id')->constrained('hashtags', 'hashtag_id')->onDelete('cascade');
            $table->string('pivot_type')->nullable(); // Dùng cho subtitle/dubbing nếu cần
            $table->timestamps();
        });

        // 3. Xóa các bảng cũ
        Schema::dropIfExists('movie_genre');
        Schema::dropIfExists('movie_language');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('languages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_hashtag');
        Schema::dropIfExists('hashtags');
    }
};
