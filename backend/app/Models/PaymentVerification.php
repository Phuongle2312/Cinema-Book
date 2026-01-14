<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: PaymentVerification
 * Purpose: Handle manual payment verification by Admin
 * 
 * Flow:
 * 1. User creates booking -> status = pending
 * 2. User submits payment proof (screenshot) -> PaymentVerification created
 * 3. Admin reviews and approves/rejects
 * 4. If approved -> Booking.status = confirmed
 */
class PaymentVerification extends Model
{
    protected $table = 'payment_verifications';
    protected $primaryKey = 'verification_id';

    protected $fillable = [
        'booking_id',
        'user_id',
        'verified_by',
        'status',
        'payment_proof',
        'payment_method',
        'amount',
        'customer_note',
        'admin_note',
        'submitted_at',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function verifiedByAdmin()
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    /**
     * Scopes
     */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Methods
     */

    public function approve($adminId, $note = null)
    {
        $this->update([
            'status' => 'approved',
            'verified_by' => $adminId,
            'admin_note' => $note,
            'verified_at' => now(),
        ]);

        // Also update booking status
        $this->booking->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return $this;
    }

    public function reject($adminId, $note = null)
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $adminId,
            'admin_note' => $note,
            'verified_at' => now(),
        ]);

        return $this;
    }
}
