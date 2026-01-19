<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCombo;
use App\Models\BookingSeat;
use App\Models\Seat;
use App\Models\SeatLock;
use App\Models\Showtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Offer;

class BookingService
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Attempt to hold seats for a user.
     *
     * @param User $user
     * @param Showtime $showtime
     * @param array $seatIds
     * @return bool
     * @throws Exception
     */
    public function holdSeats(User $user, Showtime $showtime, array $seatIds): bool
    {
        // 0. Check if Showtime is in the past
        if ($showtime->start_time->isPast()) {
            throw new Exception("Cannot book tickets for a past showtime.");
        }

        // 1. Check if seats are already booked
        $bookedSeats = BookingSeat::where('showtime_id', $showtime->showtime_id)
            ->whereIn('seat_id', $seatIds)
            ->whereHas('booking', function ($q) {
                // Consider confirmed, pending_verification, and VALID pending bookings as occupied
                $q->whereIn('status', ['confirmed', 'pending_verification'])
                    ->orWhere(function ($subQ) {
                    $subQ->where('status', 'pending')
                        ->where('expires_at', '>', Carbon::now());
                });
            })
            ->exists();

        if ($bookedSeats) {
            throw new Exception("One or more seats are already booked.");
        }

        // 2. Check if seats are locked by OTHER users
        $lockedSeats = SeatLock::where('showtime_id', $showtime->showtime_id)
            ->whereIn('seat_id', $seatIds)
            ->where('user_id', '!=', $user->id) // Ignore locks owned by THIS user (refreshing lock)
            ->where('expires_at', '>', Carbon::now())
            ->exists();

        if ($lockedSeats) {
            throw new Exception("One or more seats are currently held by another user.");
        }

        // 3. Create or Update Locks
        DB::transaction(function () use ($user, $showtime, $seatIds) {
            foreach ($seatIds as $seatId) {
                SeatLock::updateOrCreate(
                    [
                        'seat_id' => $seatId,
                        'showtime_id' => $showtime->showtime_id,
                    ],
                    [
                        'user_id' => $user->id,
                        'expires_at' => Carbon::now()->addMinutes((int) config('app.seat_lock_timeout', 6))
                    ]
                );
            }
        });

        return true;
    }

    /**
     * Create a pending booking from held seats.
     *
     * @param User $user
     * @param Showtime $showtime
     * @param array $seatIds
     * @param array $comboItems
     * @return Booking
     * @throws Exception
     */
    public function createBooking(User $user, Showtime $showtime, array $seatIds, array $comboItems = []): Booking
    {
        // 1. Auto-hold seats (Validate availability & Create locks)
        // This ensures that if the frontend didn't call /hold API, we still lock and validate here.
        $this->holdSeats($user, $showtime, $seatIds);

        // 2. Calculate Prices
        $seats = Seat::whereIn('seat_id', $seatIds)->get();
        $seatsTotal = $this->pricingService->calculateTotalSeatPrice($showtime, $seats);
        $comboTotal = $this->pricingService->calculateComboPrice($comboItems);
        $totalPrice = $seatsTotal + $comboTotal;

        // 3. Create Booking Transaction
        return DB::transaction(function () use ($user, $showtime, $seats, $comboItems, $seatsTotal, $comboTotal, $totalPrice) {
            // 3. Check for Automatic System-Wide Offers
            // DISABLED BY USER REQUEST: No discounts allowed for this project.
            $discountAmount = 0;
            $appliedOfferId = null;

            /*
            // Fetch active system-wide offers (Explicitly NO CODE by updated scope)
            $systemOffers = Offer::systemWide()
                ->orderBy('discount_value', 'desc')
                ->get();

            // Find BEST offer
            $bestOffer = null;
            $maxDiscount = 0;

            foreach ($systemOffers as $offer) {
                if ($offer->isValid()) {
                    // Check min purchase amount
                    if ($offer->min_purchase_amount && $totalPrice < $offer->min_purchase_amount) {
                        continue;
                    }

                    $discount = $offer->calculateDiscount($totalPrice);
                    if ($discount > $maxDiscount) {
                        $maxDiscount = $discount;
                        $bestOffer = $offer;
                    }
                }
            }

            if ($bestOffer) {
                $discountAmount = $maxDiscount;
                $appliedOfferId = $bestOffer->offer_id; // Correct Primary Key Use directly
                // \Illuminate\Support\Facades\Log::info("Applied Auto Offer: {$bestOffer->title} - Discount: $discountAmount");
            }
            */

            // Final Price
            $finalPrice = max(0, $totalPrice - $discountAmount);

            // Create Booking Record
            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id' => $showtime->showtime_id,
                'total_seats' => $seats->count(),
                'seats_total' => $seatsTotal,
                'combo_total' => $comboTotal,
                'total_price' => $finalPrice, // Discounted Price
                'discount_amount' => $discountAmount,
                'offer_id' => $appliedOfferId,
                'status' => 'pending', // Waiting for payment
                // expires_at is set in model boot
            ]);

            if ($appliedOfferId) {
                // Increment usage? Or wait for confirmation?
                // Usually increment on confirm. But for 'max_uses' sanity specific to pending, maybe?
                // We'll increment on confirm() in Booking model or PaymentService.
            }

            // Cleanup Zombie Seats (Expired/Cancelled bookings that still hold BookingSeat records)
            // This prevents "Integrity constraint violation: 1062 Duplicate entry"
            BookingSeat::where('showtime_id', $showtime->showtime_id)
                ->whereIn('seat_id', $seats->pluck('seat_id'))
                ->whereHas('booking', function ($q) {
                    $q->whereIn('status', ['expired', 'cancelled']);
                })
                ->delete();

            // Create Booking Seats
            foreach ($seats as $seat) {
                BookingSeat::create([
                    'booking_id' => $booking->booking_id,
                    'seat_id' => $seat->seat_id,
                    'showtime_id' => $showtime->showtime_id,
                    'price' => $this->pricingService->calculateTicketPrice($showtime, $seat),
                ]);
            }

            // Create Booking Combos
            if (!empty($comboItems)) {
                // Assuming pricing service logic or fetch again. 
                // For simplified logic, we fetch combo prices here or assume passed data is valid.
                // Best practice: Re-fetch combo prices.
                $comboIds = array_column($comboItems, 'id');
                $combos = \App\Models\Combo::whereIn('combo_id', $comboIds)->get()->keyBy('combo_id');

                foreach ($comboItems as $item) {
                    if (isset($combos[$item['id']])) {
                        BookingCombo::create([
                            'booking_id' => $booking->booking_id,
                            'combo_id' => $item['id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $combos[$item['id']]->price,
                            'total_price' => $combos[$item['id']]->price * $item['quantity'],
                        ]);
                    }
                }
            }

            // NOTE: We do NOT delete seat_locks yet. They will be deleted when Payment is Confirmed.
            // OR if we want to treat 'Pending Booking' as a lock, we can delete them now.
            // However, the common pattern is: 
            // - SeatLock keeps seat for 5 mins for SELECTION.
            // - Booking (Pending) keeps seat for 10-15 mins for PAYMENT.
            // So we can keep locks or upgrade them. 
            // Implementation Decision: Refresh lock expiry to match booking expiry?
            // For now, let's keep locks in DB but maybe extend them or rely on 'Booking' existence check in 'holdSeats' to block others.
            // In 'holdSeats', we checked 'bookedSeats' which looks at Booking table. So once Booking is created, it IS blocked.

            // So we CAN delete SeatLocks now to keep table clean, OR keep them as redundancy.
            // Let's delete them to avoid confusion, since Booking(Pending) now acts as the lock.
            SeatLock::where('showtime_id', $showtime->showtime_id)
                ->whereIn('seat_id', $seats->pluck('seat_id'))
                ->delete();

            return $booking;
        });
    }
    /**
     * Generate QR Code string (or URL) for the ticket.
     *
     * @param string $code
     * @return string
     */
    public function generateQRCode(string $code): string
    {
        // For simplicity, using a public API to generate QR code image URL.
        // In production, use a library like simplesoftwareio/simple-qrcode
        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $code;
    }
}
