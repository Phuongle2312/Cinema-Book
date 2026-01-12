<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model: Showtime
 * Mục đích: Quản lý lịch chiếu phim
 */
class Showtime extends Model
{
    use HasFactory;
    protected $primaryKey = 'showtime_id';

    protected $fillable = [
        'movie_id',
        'room_id',
        'start_time',
        'base_price',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'base_price' => 'decimal:0',
    ];

    /**
     * Accessor: Tự động tính end_time nếu null
     */
    public function getEndTimeAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value);
        }

        if ($this->start_time && $this->movie) {
            return $this->start_time->copy()->addMinutes($this->movie->duration);
        }

        return $this->start_time;
    }

    /**
     * Relationships
     */

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'showtime_id', 'showtime_id');
    }

    public function seatLocks()
    {
        return $this->hasMany(SeatLock::class, 'showtime_id', 'showtime_id');
    }

    /**
     * Scopes
     */

    public function scopeByMovie($query, $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', Carbon::now());
    }
}
