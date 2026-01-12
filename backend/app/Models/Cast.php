<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cast extends Model
{
    use HasFactory;

    protected $table = 'cast';
    protected $primaryKey = 'cast_id';

    protected $fillable = [
        'name',
        'type',
        'avatar',
        'bio',
    ];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_cast', 'cast_id', 'movie_id')
            ->withPivot('role', 'character_name');
    }
}
