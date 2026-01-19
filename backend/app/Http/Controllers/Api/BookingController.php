<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SeatLock;
use App\Models\Showtime;
use App\Models\Transaction;
use App\Models\VerifyPayment;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Offer;

/**
 * BookingController
 * Xử lý toàn bộ logic đặt vé, thanh toán và vé điện tử
 */
class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * POST /api/bookings
     * Tạo đơn đặt vé mới (khóa ghế trong 6 phút)
     */
    public function store(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,showtime_id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'exists:seats,seat_id',
            'combos' => 'nullable|array',
            'combos.*.id' => 'required_with:combos|exists:combos,combo_id',
            'combos.*.quantity' => 'required_with:combos|integer|min:1',
        ]);

        try {
            $user = $request->user();
            $showtime = Showtime::with(['movie', 'room.theater'])->findOrFail($request->showtime_id);
            $seatIds = $request->seat_ids;
            $combos = $request->combos ?? [];

            // 1. Hold Seats (Logic concurrency nằm trong Service)
            // Nếu muốn tách bước Hold và Booking ra 2 API riêng thì gọi holdSeats ở API khác.
            // Nhưng theo flow hiện tại: "Tạo booking -> Pending -> Thanh toán", nên ta làm gộp.
            // Tuy nhiên, để chặt chẽ, ta có thể gọi holdSeats trước.

            // $this->bookingService->holdSeats($user, $showtime, $seatIds);

            // 2. Create Booking (Pending)
            // Service sẽ tự validate lại lock hoặc availability
            $booking = $this->bookingService->createBooking($user, $showtime, $seatIds, $combos);

            // Load relationships để trả về
            $booking->load(['seats', 'combos', 'showtime.movie', 'showtime.room.theater']);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully. Please pay within 6 minutes.',
                'data' => $booking,
                'expires_at' => $booking->expires_at ? $booking->expires_at->toISOString() : null,
                'remaining_seconds' => $booking->remaining_time * 60,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/bookings/hold
     * API để giữ ghế tạm thời (cho bước chọn ghế trên Frontend)
     */
    public function hold(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,showtime_id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'exists:seats,seat_id',
        ]);

        try {
            $user = $request->user();
            $showtime = Showtime::findOrFail($request->showtime_id);

            $this->bookingService->holdSeats($user, $showtime, $request->seat_ids);

            return response()->json([
                'success' => true,
                'message' => 'Seats held successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/bookings/{id}
     * Lấy chi tiết booking (màn hình thanh toán)
     */
    public function show($id, Request $request)
    {
        $booking = Booking::with(['seats', 'combos', 'showtime.movie', 'showtime.room.theater', 'offer'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * POST /api/bookings/{id}/pay
     * Xử lý thanh toán (dummy payment)
     */
    public function pay($id, Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,credit_card,momo,zalopay,vnpay',
        ]);

        $booking = Booking::with(['seats', 'showtime'])->findOrFail($id);

        // Kiểm tra quyền sở hữu
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Kiểm tra trạng thái
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is not pending',
            ], 400);
        }

        // Kiểm tra hết hạn
        if ($booking->isExpired()) {
            $booking->markAsExpired();
            return response()->json([
                'success' => false,
                'message' => 'Booking expired',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Manual Verification Flow
            // Update status to 'pending_verification' instead of confirming immediately
            $booking->update(['status' => 'pending_verification']);

            // Create Transaction
            $transaction = Transaction::create([
                'booking_id' => $booking->booking_id,
                'user_id' => $booking->user_id,
                'transaction_code' => 'TXN' . date('YmdHis') . rand(1000, 9999),
                'amount' => $booking->total_price,
                'payment_method' => $request->payment_method,
                'status' => 'success', // Money transferred
                'paid_at' => now(),
            ]);

            // Create VerifyPayment Request (Pending Admin Review)
            VerifyPayment::create([
                'booking_id' => $booking->booking_id,
                'user_id' => $booking->user_id,
                'transaction_code' => $transaction->transaction_code,
                'status' => 'pending', // Waiting for Admin
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful. Waiting for Admin verification to issue ticket.',
                'data' => [
                    'booking' => $booking,
                    'transaction' => $transaction,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/bookings/{id}/apply-offer
     * Áp dụng mã giảm giá (Voucher) - Thay thế Auto Offer nếu có
     */
    public function applyOffer($id, Request $request)
    {
        $request->validate([
            'offer_code' => 'required|string',
        ]);

        $user = $request->user();
        $booking = Booking::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        // 1. Find Offer by Code
        $offer = Offer::where('code', $request->offer_code)
            ->active() // Ensure it is active
            ->first();

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Mã voucher không hợp lệ hoặc đã hết hạn.',
            ], 400);
        }

        // 2. Validate Offer Conditions
        // Date check (Covered by scopeActive but double check specific custom rules if needed)

        // Usage Limit
        if ($offer->max_uses && $offer->current_uses >= $offer->max_uses) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'], 400);
        }

        // Min Purchase
        $subtotal = $booking->seats_total + $booking->combo_total;

        if ($offer->min_purchase_amount && $subtotal < $offer->min_purchase_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng cần tối thiểu ' . number_format($offer->min_purchase_amount) . ' VND để áp dụng mã này.'
            ], 400);
        }

        // 3. Calculate Discount
        $discountAmount = $offer->calculateDiscount($subtotal);

        // 4. Update Booking (REPLACE existing offer)
        $booking->offer_id = $offer->offer_id;
        $booking->discount_amount = $discountAmount;
        $booking->total_price = max(0, $subtotal - $discountAmount);
        $booking->save();

        // Refresh to get full data
        $booking->load(['seats', 'combos', 'showtime.movie', 'showtime.room.theater', 'offer']);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'data' => $booking,
        ]);
    }

    /**
     * POST /api/bookings/{id}/remove-offer
     * Hủy áp dụng mã giảm giá -> Tự động tính lại Auto Offer (nếu có)
     */
    public function removeOffer($id, Request $request)
    {
        $user = $request->user();
        $booking = Booking::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        // Reset to raw total
        $subtotal = $booking->seats_total + $booking->combo_total;

        // Re-calculate Auto Offer (System Wide) logic
        // We can reuse the logic from BookingService or simple Check here.
        // For DRY, ideally move "calculateBestAutoOffer" to a Service method.
        // For now, implementing simplified check here.

        $systemOffers = Offer::systemWide()->orderBy('discount_value', 'desc')->get();
        $bestOffer = null;
        $maxDiscount = 0;

        foreach ($systemOffers as $offer) {
            if ($offer->isValid()) {
                if ($offer->min_purchase_amount && $subtotal < $offer->min_purchase_amount) {
                    continue;
                }
                $discount = $offer->calculateDiscount($subtotal);
                if ($discount > $maxDiscount) {
                    $maxDiscount = $discount;
                    $bestOffer = $offer;
                }
            }
        }

        if ($bestOffer) {
            $booking->offer_id = $bestOffer->offer_id;
            $booking->discount_amount = $maxDiscount;
            $booking->total_price = max(0, $subtotal - $maxDiscount);
            $msg = 'Đã hủy voucher. Hệ thống tự động áp dụng khuyến mãi tốt nhất hiện có.';
        } else {
            $booking->offer_id = null;
            $booking->discount_amount = 0;
            $booking->total_price = $subtotal;
            $msg = 'Đã hủy áp dụng mã giảm giá.';
        }

        $booking->save();
        $booking->load(['seats', 'combos', 'showtime.movie', 'showtime.room.theater', 'offer']);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => $booking
        ]);
    }

    /**
     * GET /api/bookings/e-ticket/{id}
     * Tạo vé điện tử
     */
    public function eTicket($id)
    {
        $booking = Booking::with([
            'user',
            'showtime.movie',
            'showtime.room.theater',
            'seats',
            'combos',
            'transaction',
        ])->findOrFail($id);

        // Kiểm tra booking đã confirmed hoặc pending_verification
        if ($booking->status !== 'confirmed' && $booking->status !== 'pending_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not paid or expired',
            ], 400);
        }

        // Format dữ liệu e-ticket
        $eTicket = [
            'booking_code' => $booking->booking_code,
            'status' => $booking->status, // Add status here
            'qr_code' => $this->bookingService->generateQRCode($booking->booking_code),
            'movie' => [
                'title' => $booking->showtime->movie->title,
                'poster' => $booking->showtime->movie->poster_url,
                'duration' => $booking->showtime->movie->duration,
            ],
            'showtime' => [
                'date' => $booking->showtime->start_time->format('d/m/Y'),
                'time' => $booking->showtime->start_time->format('H:i'),
                'end_time' => $booking->showtime->end_time->format('H:i'),
            ],
            'theater' => [
                'name' => $booking->showtime->room->theater->name,
                'address' => $booking->showtime->room->theater->address,
                'room' => $booking->showtime->room->name,
            ],
            'seats' => $booking->seats->map(function ($seat) {
                return [
                    'row' => $seat->row,
                    'number' => $seat->number,
                    'label' => $seat->row . $seat->number,
                ];
            }),
            'combos' => $booking->combos->map(function ($combo) {
                return [
                    'name' => $combo->name,
                    'quantity' => $combo->pivot->quantity,
                ];
            }),
            'payment' => [
                'total_price' => $booking->total_price,
                'seats_total' => $booking->seats_total,
                'combo_total' => $booking->combo_total,
                'payment_method' => $booking->transaction->payment_method ?? 'N/A',
                'paid_at' => $booking->transaction->paid_at ?? null,
            ],
            'user' => [
                'name' => $booking->user->name,
                'email' => $booking->user->email,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $eTicket,
        ]);
    }

    /**
     * GET /api/user/bookings
     * Lấy lịch sử đặt vé của user
     */
    public function userBookings(Request $request)
    {
        $user = $request->user();

        $query = Booking::with([
            'showtime.movie',
            'showtime.room.theater',
            'seats',
            'transaction',
        ])->where('user_id', $user->id);

        // Filter by Type (Upcoming / Past)
        if ($request->has('type')) {
            $now = now();
            if ($request->type === 'upcoming') {
                $query->whereHas('showtime', function ($q) use ($now) {
                    $q->where('start_time', '>', $now);
                });
            } elseif ($request->type === 'past') {
                $query->whereHas('showtime', function ($q) use ($now) {
                    $q->where('start_time', '<=', $now);
                });
            }
        }

        // Filter by Status (existing)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }
}
