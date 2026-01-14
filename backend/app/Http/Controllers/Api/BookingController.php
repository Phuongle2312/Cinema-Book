<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCombo;
use App\Models\BookingSeat;
use App\Models\Seat;
use App\Models\SeatLock;
use App\Models\Showtime;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BookingController
 * Xử lý toàn bộ logic đặt vé, thanh toán và vé điện tử
 */
class BookingController extends Controller
{
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
            'combos.*.combo_id' => 'required_with:combos|exists:combos,combo_id',
            'combos.*.quantity' => 'required_with:combos|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $showtime = Showtime::with('movie')->findOrFail($request->showtime_id);
            $seatIds = $request->seat_ids;

            // 1. Kiểm tra ghế có available không
            $this->validateSeatsAvailability($showtime->showtime_id, $seatIds);

            // 2. Tạo booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id' => $showtime->showtime_id,
                'booking_code' => $this->generateBookingCode(),
                'total_seats' => count($seatIds),
                'seats_total' => 0, // Sẽ tính sau
                'combo_total' => 0, // Sẽ tính sau
                'total_price' => 0, // Sẽ tính sau
                'status' => 'pending',
                'expires_at' => Carbon::now()->addMinutes(6),
            ]);

            // 3. Tạo booking seats và tính tổng tiền ghế
            $seatsTotal = $this->createBookingSeats($booking, $seatIds, $showtime);

            // 4. Tạo seat locks (khóa ghế trong 6 phút)
            $this->createSeatLocks($user->id, $seatIds, $showtime->showtime_id);

            // 5. Xử lý combos nếu có
            $comboTotal = 0;
            if ($request->has('combos')) {
                $comboTotal = $this->addCombosToBooking($booking, $request->combos);
            }

            // 6. Cập nhật tổng tiền (bao gồm tự động áp dụng ưu đãi hệ thống)
            $totalPrice = $seatsTotal + $comboTotal;

            // Tìm các ưu đãi hệ thống đang active
            $systemOffers = \App\Models\Offer::systemWide()->get();
            $totalDiscount = 0;

            foreach ($systemOffers as $offer) {
                if (! $offer->min_purchase_amount || $totalPrice >= $offer->min_purchase_amount) {
                    $totalDiscount += $offer->calculateDiscount($totalPrice - $totalDiscount);
                }
            }

            $booking->update([
                'seats_total' => $seatsTotal,
                'combo_total' => $comboTotal,
                'total_price' => max(0, $totalPrice - $totalDiscount),
            ]);

            DB::commit();

            // Load relationships để trả về
            $booking->load(['seats', 'combos', 'showtime.movie', 'showtime.room.theater']);

            return response()->json([
                'success' => true,
                'message' => 'Đặt vé thành công. Vui lòng thanh toán trong 6 phút.',
                'data' => $booking,
                'expires_at' => $booking->expires_at->toISOString(),
                'remaining_seconds' => $booking->expires_at->diffInSeconds(now()),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Đặt vé thất bại: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/bookings/{id}
     * Lấy chi tiết booking (màn hình thanh toán)
     */
    public function show($id, Request $request)
    {
        $booking = Booking::with(['seats', 'combos', 'showtime.movie', 'showtime.room.theater'])
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

        $booking = Booking::with(['seats', 'combos', 'showtime.movie', 'showtime.room.theater'])->findOrFail($id);

        // Kiểm tra quyền sở hữu
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thanh toán booking này',
            ], 403);
        }

        // Kiểm tra trạng thái
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking này đã được xử lý',
            ], 400);
        }

        // Kiểm tra hết hạn
        if ($booking->expires_at && $booking->expires_at->isPast()) {
            $booking->update(['status' => 'expired']);

            return response()->json([
                'success' => false,
                'message' => 'Booking đã hết hạn',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Tạo transaction
            $transaction = Transaction::create([
                'booking_id' => $booking->booking_id,
                'user_id' => $booking->user_id,
                'transaction_code' => $this->generateTransactionCode(),
                'amount' => $booking->total_price,
                'payment_method' => $request->payment_method,
                'status' => 'success', // Dummy payment - luôn thành công
                'paid_at' => now(),
            ]);

            // 2. Cập nhật booking status
            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // 3. Xóa seat locks
            SeatLock::where('user_id', $booking->user_id)
                ->whereIn('seat_id', $booking->seats->pluck('seat_id'))
                ->where('showtime_id', $booking->showtime_id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công',
                'data' => [
                    'booking' => $booking,
                    'transaction' => $transaction,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Thanh toán thất bại: '.$e->getMessage(),
            ], 400);
        }
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

        // Kiểm tra booking đã confirmed
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Vé chưa được thanh toán',
            ], 400);
        }

        // Format dữ liệu e-ticket
        $eTicket = [
            'booking_code' => $booking->booking_code,
            'qr_code' => $this->generateQRCode($booking->booking_code),
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
                    'label' => $seat->row.$seat->number,
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

        // Lọc theo trạng thái
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo thời gian (upcoming/past)
        if ($request->get('type') === 'upcoming') {
            $query->whereHas('showtime', function ($q) {
                $q->where('start_time', '>', now());
            });
        } elseif ($request->get('type') === 'past') {
            $query->whereHas('showtime', function ($q) {
                $q->where('start_time', '<=', now());
            });
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

    // ==================== HELPER METHODS ====================

    /**
     * Kiểm tra ghế có available không
     */
    private function validateSeatsAvailability($showtimeId, $seatIds)
    {
        // Kiểm tra ghế đã được đặt (Confirmed) hoặc đang chờ thanh toán (Pending + Chưa hết hạn)
        $bookedSeats = BookingSeat::whereHas('booking', function ($query) use ($showtimeId) {
            $query->where('showtime_id', $showtimeId)
                ->where(function ($q) {
                    $q->where('status', 'confirmed')
                        ->orWhere(function ($sq) {
                            $sq->where('status', 'pending')
                                ->where('expires_at', '>', Carbon::now());
                        });
                });
        })->whereIn('seat_id', $seatIds)->count();

        if ($bookedSeats > 0) {
            throw new \Exception('Một số ghế đã được đặt');
        }

        // Kiểm tra ghế đang bị lock
        $lockedSeats = SeatLock::where('showtime_id', $showtimeId)
            ->whereIn('seat_id', $seatIds)
            ->where('expires_at', '>', Carbon::now())
            ->count();

        if ($lockedSeats > 0) {
            throw new \Exception('Một số ghế đang được giữ bởi người khác');
        }
    }

    /**
     * Tạo booking seats
     */
    private function createBookingSeats($booking, $seatIds, $showtime)
    {
        $total = 0;
        $seats = Seat::whereIn('seat_id', $seatIds)->get();

        foreach ($seats as $seat) {
            $price = $showtime->base_price + $seat->extra_price;

            BookingSeat::create([
                'booking_id' => $booking->booking_id,
                'seat_id' => $seat->seat_id,
                'showtime_id' => $showtime->showtime_id,
                'price' => $price,
            ]);

            $total += $price;
        }

        return $total;
    }

    /**
     * Tạo seat locks
     */
    private function createSeatLocks($userId, $seatIds, $showtimeId)
    {
        $expiresAt = Carbon::now()->addMinutes(6);

        foreach ($seatIds as $seatId) {
            SeatLock::create([
                'seat_id' => $seatId,
                'showtime_id' => $showtimeId,
                'user_id' => $userId,
                'locked_at' => now(),
                'expires_at' => $expiresAt,
            ]);
        }
    }

    /**
     * Thêm combos vào booking
     */
    private function addCombosToBooking($booking, $combos)
    {
        $total = 0;

        foreach ($combos as $comboData) {
            $combo = \App\Models\Combo::find($comboData['combo_id']);
            $quantity = $comboData['quantity'];
            $comboTotal = $combo->price * $quantity;

            BookingCombo::create([
                'booking_id' => $booking->booking_id,
                'combo_id' => $combo->combo_id,
                'quantity' => $quantity,
                'unit_price' => $combo->price,
                'total_price' => $comboTotal,
            ]);

            $total += $comboTotal;
        }

        return $total;
    }

    /**
     * Generate booking code
     */
    private function generateBookingCode()
    {
        return 'BK'.date('Ymd').str_pad(
            Booking::whereDate('created_at', today())->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate transaction code
     */
    private function generateTransactionCode()
    {
        return 'TXN'.date('YmdHis').rand(1000, 9999);
    }

    /**
     * Generate QR code data
     */
    private function generateQRCode($bookingCode)
    {
        // Trong thực tế, bạn sẽ dùng thư viện QR code
        // Ở đây chỉ return URL để generate QR
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.$bookingCode;
    }
}
