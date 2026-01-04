<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xóa cột rating từ movies và tạo database view để tính động
     */
    public function up(): void
    {
        // 1. Xóa cột rating từ bảng movies (derived attribute)
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('rating');
        });

        // 2. Tạo database view để tính rating động từ reviews
        DB::statement('
            CREATE OR REPLACE VIEW movie_ratings AS
            SELECT 
                m.movie_id,
                COALESCE(AVG(r.rating), 0) as average_rating,
                COALESCE(COUNT(r.review_id), 0) as review_count,
                COALESCE(SUM(CASE WHEN r.rating >= 8 THEN 1 ELSE 0 END), 0) as excellent_count,
                COALESCE(SUM(CASE WHEN r.rating >= 6 AND r.rating < 8 THEN 1 ELSE 0 END), 0) as good_count,
                COALESCE(SUM(CASE WHEN r.rating < 6 THEN 1 ELSE 0 END), 0) as poor_count
            FROM movies m
            LEFT JOIN reviews r ON m.movie_id = r.movie_id
            GROUP BY m.movie_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Xóa view
        DB::statement('DROP VIEW IF EXISTS movie_ratings');

        // 2. Thêm lại cột rating vào movies
        Schema::table('movies', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->default(0)->after('banner_url');
        });

        // 3. Tính lại rating từ reviews và update vào movies
        $movies = DB::table('movies')->get();
        
        foreach ($movies as $movie) {
            $avgRating = DB::table('reviews')
                ->where('movie_id', $movie->movie_id)
                ->avg('rating');
            
            DB::table('movies')
                ->where('movie_id', $movie->movie_id)
                ->update(['rating' => round($avgRating ?? 0, 1)]);
        }
    }
};
