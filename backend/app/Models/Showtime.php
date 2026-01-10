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
        'show_date',
        'show_time',
        'start_time',
        'base_price',
        'vip_price',
        'is_active',
        'available_seats',
    ];

    protected $appends = ['format'];

    protected $casts = [
        'show_date' => 'date',
        'start_time' => 'datetime',
        'base_price' => 'decimal:0',
        'vip_price' => 'decimal:0',
        'is_active' => 'boolean',
    ];

    /**
     * Accessor: Tính end_time tự động từ start_time + movie duration
     */
    public function getEndTimeAttribute()
    {
        if (!$this->start_time || !$this->movie) {
            return null;
        }
        return $this->start_time->copy()->addMinutes($this->movie->duration);
    }

    /**
     * Boot method - Tự động tính start_time và end_time
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($showtime) {
            // Tạo start_time từ show_date + show_time
            if (empty($showtime->start_time)) {
                $showtime->start_time = Carbon::parse(
                    $showtime->show_date->format('Y-m-d') . ' ' . $showtime->show_time
                );
            }

            // Set available_seats = total_seats của rooms
            if (empty($showtime->available_seats) && $showtime->room) {
                $showtime->available_seats = $showtime->room->total_seats;
            }
        });
    }

    /**
     * Relationships
     */

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function seatLocks()
    {
        return $this->hasMany(SeatLock::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByMovie($query, $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('show_date', $date);
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
        return $this->available_seats > 0;
    }

    // Lấy danh sách ghế đã đặt
    public function getBookedSeats()
    {
        return BookingDetail::whereHas('booking', function ($query) {
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
            } elseif ($rawType === 'imax') {
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
