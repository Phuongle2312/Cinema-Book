<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;
    protected $primaryKey = 'language_id';

    protected $fillable = ['name', 'code'];

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_language', 'language_id', 'movie_id')
            ->withPivot('type');
    }
}
