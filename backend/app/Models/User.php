<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Thêm Sanctum

/**
 * Model: User
 * Mục đích: Quản lý thông tin người dùng, authentication, và social login
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Thêm HasApiTokens cho Sanctum

    /**
     * Các trường có thể mass assign
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'role',
        'provider',        // google, facebook
        'provider_id',     // ID từ provider
        'user_level',      // 1: Free, 2: Bought, 3: Watched
        'avatar',          // Avatar URL
    ];

    /**
     * Các trường ẩn khi serialize
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting attributes
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'user_level' => 'integer',
        ];
    }

    /**
     * Relationships
     */

    // Một user có nhiều bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Một user có nhiều phim yêu thích (Wishlist)
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Một user có nhiều notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Một user có nhiều transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Một user có nhiều verify payments
    public function verifyPayments()
    {
        return $this->hasMany(VerifyPayment::class);
    }

    // Một user có nhiều seat locks
    public function seatLocks()
    {
        return $this->hasMany(SeatLock::class);
    }

    /**
     * Helper Methods
     */

    // Kiểm tra user có phải admin không
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Kiểm tra user đăng nhập bằng social login không
    public function isSocialLogin(): bool
    {
        return !empty($this->provider);
    }
}
