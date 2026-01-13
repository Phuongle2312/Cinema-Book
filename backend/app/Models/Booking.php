<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model: Booking
 * Mục đích: Quản lý đặt vé, giữ ghế, thanh toán
 */
class Booking extends Model
{
    use HasFactory;
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'user_id',
        'showtime_id',
        'booking_code',
        'total_seats',
        'seats_total',
        'combo_total', // Tổng giá combo
        'total_price', // Tổng giá (ghế + combo)
        'status',
        'expires_at',
        'confirmed_at',
    ];

    protected $casts = [
        'seats_total' => 'decimal:0',
        'combo_total' => 'decimal:0',
        'total_price' => 'decimal:0',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Boot method - Tự động tạo booking code và expires_at
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            // Tạo booking code unique (VD: BK20231217001)
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BK' . date('Ymd') . str_pad(
                    static::whereDate('created_at', today())->count() + 1,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
            }

            // Set expires_at = 6 phút từ bây giờ (nếu chưa set)
            if (empty($booking->expires_at) && $booking->status === 'pending') {
                $booking->expires_at = Carbon::now()->addMinutes(config('app.seat_lock_timeout', 6));
            }
        });
    }

    /**
     * Relationships
     */

    // Booking thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Booking thuộc về một showtime
    public function showtime()
    {
        return $this->belongsTo(Showtime::class, 'showtime_id', 'showtime_id');
    }

    // Booking có nhiều ghế (through booking_details)
    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'booking_seats', 'booking_id', 'seat_id')
            ->withPivot('price')
            ->withTimestamps();
    }

    // Booking có nhiều booking details (chi tiết)
    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class, 'booking_id', 'booking_id');
    }

    // Booking có một transaction
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'booking_id', 'booking_id');
    }

    // Booking có nhiều combos (through booking_combos)
    public function combos()
    {
        return $this->belongsToMany(
            Combo::class,
            'booking_combos',
            'booking_id',
            'combo_id'
        )->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }

    // Booking có nhiều booking combos (chi tiết)
    public function bookingCombos()
    {
        return $this->hasMany(BookingCombo::class, 'booking_id', 'booking_id');
    }

    // Booking có thể có một review
    public function review()
    {
        return $this->hasOne(Review::class, 'booking_id', 'booking_id');
    }

    /**
     * Scopes
     */

    // Lọc booking đang pending
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Lọc booking đã confirmed
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // Lọc booking đã expired
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Lọc booking của một user
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Lọc booking đã hết hạn (expires_at < now)
    public function scopeExpiredTime($query)
    {
        return $query->where('expires_at', '<', Carbon::now())
            ->where('status', 'pending');
    }

    /**
     * Helper Methods
     */

    // Kiểm tra booking đã hết hạn chưa
    public function isExpired(): bool
    {
        return $this->status === 'pending' &&
            $this->expires_at &&
            $this->expires_at->isPast();
    }

    // Kiểm tra booking đã confirmed chưa
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    // Kiểm tra booking đã cancelled chưa
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Xác nhận booking (sau khi thanh toán thành công)
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => Carbon::now(),
        ]);

        // Xóa seat locks
        SeatLock::where('user_id', $this->user_id)
            ->whereIn('seat_id', $this->seats->pluck('seat_id'))
            ->where('showtime_id', $this->showtime_id)
            ->delete();

        return $this;
    }

    // Hủy booking
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);

        // Release seats (xóa seat locks)
        SeatLock::where('user_id', $this->user_id)
            ->whereIn('seat_id', $this->seats->pluck('seat_id'))
            ->where('showtime_id', $this->showtime_id)
            ->delete();

        // Cập nhật available_seats của showtime
        $this->showtime->increment('available_seats', $this->total_seats);

        return $this;
    }

    // Đánh dấu booking là expired
    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);

        // Release seats
        SeatLock::where('user_id', $this->user_id)
            ->whereIn('seat_id', $this->seats->pluck('seat_id'))
            ->where('showtime_id', $this->showtime_id)
            ->delete();

        // Cập nhật available_seats
        $this->showtime->increment('available_seats', $this->total_seats);

        return $this;
    }

    // Lấy thông tin phim từ showtime
    public function getMovieAttribute()
    {
        return $this->showtime->movie;
    }

    // Lấy thông tin rạp từ showtime
    public function getTheaterAttribute()
    {
        return $this->showtime->room->theater;
    }

    // Lấy thời gian còn lại để thanh toán (phút)
    public function getRemainingTimeAttribute()
    {
        if ($this->status !== 'pending' || !$this->expires_at) {
            return 0;
        }

        $now = Carbon::now();
        if ($this->expires_at->isPast()) {
            return 0;
        }

        return $now->diffInMinutes($this->expires_at);
    }
}
