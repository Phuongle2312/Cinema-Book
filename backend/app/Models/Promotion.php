<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model: Promotion
 * Mục đích: Quản lý các mã khuyến mãi và ưu đãi
 */
class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'valid_from',
        'valid_to',
        'max_uses',
        'current_uses',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: Lấy các promotion đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_to', '>=', now())
                    ->where(function($q) {
                        $q->whereNull('max_uses')
                          ->orWhereRaw('current_uses < max_uses');
                    });
    }

    /**
     * Kiểm tra promotion còn hiệu lực không
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($now < $this->valid_from || $now > $this->valid_to) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Tính toán số tiền giảm giá
     */
    public function calculateDiscount($amount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = $amount * ($this->discount_value / 100);
            
            // Áp dụng max discount nếu có
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
            
            return $discount;
        }

        // Fixed discount
        return min($this->discount_value, $amount);
    }

    /**
     * Tăng số lần sử dụng
     */
    public function incrementUses()
    {
        $this->increment('current_uses');
    }
}
