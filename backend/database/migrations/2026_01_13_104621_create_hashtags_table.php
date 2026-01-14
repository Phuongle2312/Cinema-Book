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

        // 3. Migrate Data từ Genres và Languages sang Hashtags
        
        // Migrate Genres
        if (Schema::hasTable('genres')) {
            $genres = DB::table('genres')->get();
            foreach ($genres as $genre) {
                // Check if exists
                $exists = DB::table('hashtags')->where('name', $genre->name)->where('type', 'genre')->exists();
                if (!$exists) {
                    $newId = DB::table('hashtags')->insertGetId([
                        'name' => $genre->name,
                        'type' => 'genre',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Migrate Movie Genre Pivot
                    if (Schema::hasTable('movie_genre')) {
                        $pivots = DB::table('movie_genre')->where('genre_id', $genre->genre_id)->get();
                        foreach ($pivots as $pivot) {
                             DB::table('movie_hashtag')->insert([
                                 'movie_id' => $pivot->movie_id,
                                 'hashtag_id' => $newId,
                                 'pivot_type' => null,
                             ]);
                        }
                    }
                }
            }
        }

        // Migrate Languages
        if (Schema::hasTable('languages')) {
            $languages = DB::table('languages')->get();
            foreach ($languages as $lang) {
                $exists = DB::table('hashtags')->where('name', $lang->name)->where('type', 'language')->exists();
                if (!$exists) {
                    $newId = DB::table('hashtags')->insertGetId([
                        'name' => $lang->name,
                        'type' => 'language',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Migrate Movie Language Pivot
                    if (Schema::hasTable('movie_language')) {
                        $pivots = DB::table('movie_language')->where('language_id', $lang->language_id)->get();
                        foreach ($pivots as $pivot) {
                             DB::table('movie_hashtag')->insert([
                                 'movie_id' => $pivot->movie_id,
                                 'hashtag_id' => $newId,
                                 'pivot_type' => isset($pivot->type) ? $pivot->type : null, // subtitle/dubbing
                             ]);
                        }
                    }
                }
            }
        }

        // 4. Xóa các bảng cũ
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
