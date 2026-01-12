<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Room (trước đây là Screen)
 * Bảng: rooms
 * Mục đích: Quản lý phòng chiếu phim
 */
class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'room_id';

    protected $fillable = [
        'theater_id',
        'name',
        'total_seats',
        'screen_type',
    ];

    protected $casts = [
        'total_seats' => 'integer',
    ];

    /**
     * Relationships
     */

    // Một room thuộc về một theater
    public function theater()
    {
        return $this->belongsTo(Theater::class, 'theater_id', 'theater_id');
    }

    // Một room có nhiều seats
    public function seats()
    {
        return $this->hasMany(Seat::class, 'room_id', 'room_id');
    }

    // Một room có nhiều showtimes
    public function showtimes()
    {
        return $this->hasMany(Showtime::class, 'room_id', 'room_id');
    }
}
