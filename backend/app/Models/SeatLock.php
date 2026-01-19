<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model: SeatLock
 * Mục đích: Giữ ghế tạm thời trong 5-6 phút
 */
class SeatLock extends Model
{
    use HasFactory;

    protected $primaryKey = 'lock_id';

    protected $fillable = [
        'seat_id',
        'showtime_id',
        'user_id',
        'locked_at',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Boot method - Tự động set expires_at
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lock) {
            if (empty($lock->expires_at)) {
                // Lấy timeout từ config (mặc định 6 phút)
                $timeout = (int) config('app.seat_lock_timeout', 6);
                $lock->expires_at = Carbon::now()->addMinutes($timeout);
            }
        });
    }

    /**
     * Relationships
     */
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */

    // Lọc locks chưa hết hạn
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    // Lọc locks đã hết hạn
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Helper Methods
     */

    // Kiểm tra lock đã hết hạn chưa
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    // Cleanup expired locks (static method)
    public static function cleanupExpired()
    {
        return static::expired()->delete();
    }
}
