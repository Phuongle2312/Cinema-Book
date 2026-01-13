<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model: Movie
 * Mục đích: Quản lý thông tin phim
 */
class Movie extends Model
{
    use HasFactory;

    protected $primaryKey = 'movie_id';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'synopsis',
        'duration',
        'release_date',
        'age_rating',
        'poster_url',
        'banner_url',
        'trailer_url',
        'status',
        'is_featured',
        'base_price',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_featured' => 'boolean',
    ];

    /**
     * Boot method - Tự động tạo slug khi tạo movie
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movie) {
            if (empty($movie->slug)) {
                $movie->slug = Str::slug($movie->title);
            }
        });
    }

    /**
     * Relationships
     */

    // Quan hệ Hashtags chung
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'movie_hashtag', 'movie_id', 'hashtag_id')
            ->withPivot('pivot_type')
            ->withTimestamps();
    }

    // Lọc hashtags là Thể loại
    public function genres()
    {
        return $this->hashtags()->where('type', 'genre');
    }

    // Lọc hashtags là Ngôn ngữ
    public function languages()
    {
        return $this->hashtags()->where('type', 'language');
    }

    // Một phim có nhiều suất chiếu
    public function showtimes()
    {
        return $this->hasMany(Showtime::class, 'movie_id', 'movie_id');
    }

    // Một phim có nhiều reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'movie_id', 'movie_id');
    }

    /**
     * Scopes - Các query helper
     */

    // Lọc phim đang chiếu
    public function scopeNowShowing($query)
    {
        return $query->where('status', 'now_showing');
    }

    // Lọc phim sắp chiếu
    public function scopeComingSoon($query)
    {
        return $query->where('status', 'coming_soon');
    }

    // Lọc phim nổi bật
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Tìm kiếm phim theo title
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('title', 'like', "%{$searchTerm}%");
    }

    // Lọc theo thể loại (Hashtag type=genre)
    public function scopeByGenre($query, $genreId)
    {
        return $query->whereHas('hashtags', function ($q) use ($genreId) {
            $q->where('hashtags.hashtag_id', $genreId)->where('type', 'genre');
        });
    }

    // Lọc theo ngôn ngữ (Hashtag type=language)
    public function scopeByLanguage($query, $languageId)
    {
        return $query->whereHas('hashtags', function ($q) use ($languageId) {
            $q->where('hashtags.hashtag_id', $languageId)->where('type', 'language');
        });
    }

    /**
     * Accessors & Mutators
     */

    // Lấy rating trung bình từ reviews
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    // Lấy tổng số reviews
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    // Kiểm tra phim có đang chiếu không
    public function isNowShowing(): bool
    {
        return $this->status === 'now_showing';
    }

    // Kiểm tra phim có sắp chiếu không
    public function isComingSoon(): bool
    {
        return $this->status === 'coming_soon';
    }
}
