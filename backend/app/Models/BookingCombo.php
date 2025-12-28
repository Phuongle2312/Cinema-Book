<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: BookingCombo
 * Bảng: booking_combos
 * Mục đích: Pivot table giữa Booking và Combo
 */
class BookingCombo extends Model
{
    protected $table = 'booking_combos';
    
    protected $fillable = [
        'booking_id',
        'combo_id',
        'quantity',
        'unit_price',
        'total_price',
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:0',
        'total_price' => 'decimal:0',
    ];
    
    /**
     * Relationship: Thuộc về một booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }
    
    /**
     * Relationship: Thuộc về một combo
     */
    public function combo()
    {
        return $this->belongsTo(Combo::class, 'combo_id', 'combo_id');
    }
}
