<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $table = 'booking_details';
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'booking_id',
        'seat_id',
        'showtime_id',
        'ticket_code',
        'base_price',
        'seat_extra_price',
        'final_price',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:0',
        'seat_extra_price' => 'decimal:0',
        'final_price' => 'decimal:0',
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
