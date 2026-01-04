<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: Combo
 * Bảng: combos
 * Mục đích: Quản lý các combo đồ ăn, nước uống
 */
class Combo extends Model
{
    protected $table = 'combos';
    protected $primaryKey = 'combo_id';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'is_available',
    ];
    
    protected $casts = [
        'price' => 'decimal:0',
        'is_available' => 'boolean',
    ];
    
    /**
     * Relationship: Combo có nhiều items
     */
    public function items()
    {
        return $this->hasMany(ComboItem::class, 'combo_id', 'combo_id');
    }
    
    /**
     * Relationship: Combo được đặt trong nhiều bookings (qua booking_combos)
     */
    public function bookings()
    {
        return $this->belongsToMany(
            Booking::class,
            'booking_combos',
            'combo_id',
            'booking_id'
        )->withPivot('quantity', 'unit_price', 'total_price')
         ->withTimestamps();
    }
    
    /**
     * Relationship: Chi tiết booking_combos
     */
    public function bookingCombos()
    {
        return $this->hasMany(BookingCombo::class, 'combo_id', 'combo_id');
    }
}
