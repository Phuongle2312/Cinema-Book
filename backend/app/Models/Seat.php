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

    protected $fillable = [
        'room_id',
        'row',
        'number',
        'seat_code',
        'seat_type',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Boot method - Tự động tạo seat_code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($seat) {
            if (empty($seat->seat_code)) {
                $seat->seat_code = $seat->row . $seat->number;
            }
        });
    }

    /**
     * Relationships
     */

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_details', 'seat_id', 'booking_id')
            ->withPivot('price', 'status')
            ->withTimestamps();
    }

    public function seatLocks()
    {
        return $this->hasMany(SeatLock::class);
    }

    /**
     * Scopes
     */

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('seat_type', $type);
    }

    /**
     * Helper Methods
     */

    // Kiểm tra ghế có bị đặt trong showtime này không
    public function isBookedForShowtime($showtimeId): bool
    {
        return BookingDetail::whereHas('booking', function ($query) use ($showtimeId) {
            $query->where('showtime_id', $showtimeId)
                  ->whereIn('status', ['pending', 'confirmed']);
        })->where('seat_id', $this->seat_id)->exists();
    }

    // Kiểm tra ghế có bị lock trong showtime này không
    public function isLockedForShowtime($showtimeId): bool
    {
        return SeatLock::where('seat_id', $this->id)
            ->where('showtime_id', $showtimeId)
            ->where('expires_at', '>', now())
            ->exists();
    }
}
