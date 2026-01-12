<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSeat extends Model
{
    use HasFactory;

    protected $table = 'booking_seats';
    protected $primaryKey = 'id';

    protected $fillable = [
        'booking_id',
        'seat_id',
        'showtime_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:0',
    ];

    /**
     * Relationship: Seat thuộc về một booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    /**
     * Relationship: Ghế cụ thể
     */
    public function seat()
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }

    /**
     * Relationship: Suất chiếu
     */
    public function showtime()
    {
        return $this->belongsTo(Showtime::class, 'showtime_id', 'showtime_id');
    }
}
