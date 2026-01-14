<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: MovieDiscount
 * Purpose: Admin-controlled discounts for specific movies
 */
class MovieDiscount extends Model
{
    protected $table = 'movie_discounts';
    protected $primaryKey = 'discount_id';

    protected $fillable = [
        'movie_id',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:0',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeForMovie($query, $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    /**
     * Calculate discount amount
     * 
     * @param float $originalPrice
     * @return float
     */
    public function calculateDiscount($originalPrice)
    {
        if ($this->discount_type === 'percentage') {
            $discount = $originalPrice * ($this->discount_value / 100);
            
            // Apply max discount cap if exists
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
            
            return $discount;
        }

        // Fixed discount
        return min($this->discount_value, $originalPrice);
    }

    /**
     * Get final price after discount
     */
    public function getFinalPrice($originalPrice)
    {
        return $originalPrice - $this->calculateDiscount($originalPrice);
    }

    /**
     * Check if discount is currently valid
     */
    public function isValid()
    {
        return $this->is_active 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }
}
