<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model: Showtime
 * Mục đích: Quản lý lịch chiếu phim
 */
class Showtime extends Model
{
    use HasFactory;
    protected $primaryKey = 'showtime_id';

    protected $fillable = [
        'movie_id',
        'room_id',
        'start_time',
        'base_price',
        'status',
    ];

    protected $appends = ['format'];

    protected $casts = [
        'start_time' => 'datetime',
        'base_price' => 'decimal:0',
    ];

    /**
     * Accessor: Tự động tính end_time nếu null
     */
    public function getEndTimeAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value);
        }

        if ($this->start_time && $this->movie) {
            return $this->start_time->copy()->addMinutes($this->movie->duration);
        }

        return $this->start_time;
    }

    /**
     * Relationships
     */

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'showtime_id', 'showtime_id');
    }

    public function seatLocks()
    {
        return $this->hasMany(SeatLock::class, 'showtime_id', 'showtime_id');
    }

    /**
     * Scopes
     */

    public function scopeByMovie($query, $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', Carbon::now());
    }

    /**
     * Helper Methods
     */

    // Kiểm tra còn ghế trống không
    public function hasAvailableSeats(): bool
    {
        $totalSeats = $this->room ? $this->room->total_seats : 0;
        $bookedCount = $this->getBookedSeats()->count();
        return ($totalSeats - $bookedCount) > 0;
    }

    // Lấy danh sách ghế đã đặt
    public function getBookedSeats()
    {
        return BookingSeat::whereHas('booking', function ($query) {
            $query->where('showtime_id', $this->showtime_id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id');
    }

    // Lấy danh sách ghế đang bị lock
    public function getLockedSeats()
    {
        return $this->seatLocks()
            ->where('expires_at', '>', Carbon::now())
            ->pluck('seat_id');
    }

    // Accessor: Format (2D/3D + Language) cho Frontend
    public function getFormatAttribute()
    {
        // Logic: Lấy Type của Room + Language dựa trên ID
        // Chẵn = Phụ Đề Anh, Lẻ = Phụ Đề Việt (giả lập để có dữ liệu 2 tab)

        $type = '2D';
        if ($this->room && !empty($this->room->screen_type)) {
            $rawType = $this->room->screen_type;
            if ($rawType === 'standard') {
                $type = '2D';
            } elseif ($rawType === 'imax' || $rawType === 'IMAX') {
                $type = 'IMAX';
            } else {
                $type = ucfirst($rawType);
            }
        }

        // Return format string matches Frontend hardcoded tabs
        // "2D Phụ Đề Anh", "2D Phụ Đề Việt"
        $lang = ($this->showtime_id % 2 == 0) ? "Phụ Đề Anh" : "Phụ Đề Việt";

        return "$type $lang";
    }
}
