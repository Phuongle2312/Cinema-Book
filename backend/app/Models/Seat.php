<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: Seat
 * Mục đích: Quản lý ghế ngồi trong phòng chiếu
 */
class Seat extends Model
{
    use HasFactory;
    protected $primaryKey = 'seat_id';

    protected $fillable = [
        'room_id',
        'row',
        'number',
        'seat_code',
        'seat_type',
        'extra_price',
        'is_available',
    ];

    /**
     * Relationships
     */

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_details', 'seat_id', 'booking_id')
            ->withPivot('final_price')
            ->withTimestamps();
    }

    public function seatLocks()
    {
        return $this->hasMany(SeatLock::class, 'seat_id', 'seat_id');
    }

    /**
     * Scopes
     */

    public function scopeByType($query, $type)
    {
        return $query->where('seat_type', $type);
    }
}
