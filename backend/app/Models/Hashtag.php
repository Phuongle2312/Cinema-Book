<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    use HasFactory;

    protected $table = 'hashtags';
    protected $primaryKey = 'hashtag_id';

    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * Relationship: Các phim có hashtag này
     */
    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_hashtag', 'hashtag_id', 'movie_id')
            ->withPivot('pivot_type')
            ->withTimestamps();
    }
}
