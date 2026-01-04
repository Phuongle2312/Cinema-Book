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
        'price',
        'status',
        'used_at',
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'used_at' => 'datetime',
    ];

    /**
     * Relationship: Detail thuộc về một booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    /**
     * Relationship: Detail tương ứng với một ghế
     */
    public function seat()
    {
        return $this->belongsTo(Seat::class, 'seat_id', 'seat_id');
    }
}
