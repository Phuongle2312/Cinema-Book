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

    // Một phim thuộc nhiều thể loại (many-to-many)
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genre', 'movie_id', 'genre_id');
    }

    // Một phim có nhiều ngôn ngữ (many-to-many)
    public function languages()
    {
        return $this->belongsToMany(Language::class, 'movie_language', 'movie_id', 'language_id')
            ->withPivot('type'); // subtitle hoặc dubbing
    }

    // Một phim có nhiều diễn viên/đạo diễn (many-to-many)
    public function cast()
    {
        return $this->belongsToMany(Cast::class, 'movie_cast')
            ->withPivot('role', 'character_name', 'order')
            ->orderBy('order');
    }

    // Lấy chỉ diễn viên
    public function actors()
    {
        return $this->belongsToMany(Cast::class, 'movie_cast')
            ->wherePivot('role', 'actor')
            ->withPivot('character_name', 'order')
            ->orderBy('order');
    }

    // Lấy chỉ đạo diễn
    public function directors()
    {
        return $this->belongsToMany(Cast::class, 'movie_cast')
            ->wherePivot('role', 'director')
            ->withPivot('order')
            ->orderBy('order');
    }

    // Một phim có nhiều suất chiếu
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }

    // Một phim có nhiều reviews
    public function reviews()
    {
        return $this->hasMany(Review::class);
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

    // Tìm kiếm phim theo title, actor, director
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
                ->orWhereHas('cast', function ($castQuery) use ($searchTerm) {
                    $castQuery->where('name', 'like', "%{$searchTerm}%");
                });
        });
    }

    // Lọc theo thể loại
    public function scopeByGenre($query, $genreId)
    {
        return $query->whereHas('genres', function ($q) use ($genreId) {
            $q->where('genres.id', $genreId);
        });
    }

    // Lọc theo ngôn ngữ
    public function scopeByLanguage($query, $languageId)
    {
        return $query->whereHas('languages', function ($q) use ($languageId) {
            $q->where('languages.id', $languageId);
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
