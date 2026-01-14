<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingCombo;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\SeatLock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\PricingService;

/**
 * BookingController
 * Xử lý toàn bộ logic đặt vé, thanh toán và vé điện tử
 */
class BookingController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * POST /api/bookings/hold
     * Giữ ghế tạm thời (Phase 2)
     */
    public function hold(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,showtime_id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'exists:seats,seat_id',
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $showtimeId = $request->showtime_id;
            $seatIds = $request->seat_ids;

            // 1. Validate: Ghế phải trống (chưa book, chưa lock bởi AI)
            // Nếu lock bởi chính mình thì gia hạn (update expires_at)
            $this->validateSeatsForHold($showtimeId, $seatIds, $user->id);

            // 2. Tạo hoặc update SeatLock
            // Xóa lock cũ của chính user này cho các ghế đó (nếu có) để tạo mới cho sạch, hoặc update
            // Ở đây ta xóa lock cũ của user cho ghế này để tạo lock mới
            SeatLock::where('showtime_id', $showtimeId)
                ->where('user_id', $user->id)
                ->whereIn('seat_id', $seatIds)
                ->delete();

            $this->createSeatLocks($user->id, $seatIds, $showtimeId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Giữ ghế thành công.',
                'expires_at' => Carbon::now()->addMinutes(config('app.seat_lock_timeout', 6))->toISOString(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Không thể giữ ghế: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/bookings
     * Tạo đơn đặt vé mới từ các ghế đã hold (Phase 3)
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
            \Log::info('Booking store attempt', ['user' => $user->id, 'showtime_id' => $request->showtime_id, 'seats' => $request->seat_ids]);
            $showtime = Showtime::with(['movie', 'room'])->findOrFail($request->showtime_id);
            $seatIds = $request->seat_ids;

            // 1. Kiểm tra User có đang giữ lock cho các ghế này không
            // 1. Kiểm tra User có đang giữ lock cho các ghế này không. 
            // Nếu chưa, thử hold luôn (tiện cho frontend).
            try {
                $this->validateLocksOwnership($showtime->showtime_id, $seatIds, $user->id);
            } catch (\Exception $e) {
                // Thử hold ngay lúc này nếu ghế vẫn trống
                $this->validateSeatsForHold($showtime->showtime_id, $seatIds, $user->id);
                $this->createSeatLocks($user->id, $seatIds, $showtime->showtime_id);
            }

            // 2. Tạo booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'showtime_id' => $showtime->showtime_id,
                'booking_code' => $this->generateBookingCode(),
                'total_seats' => count($seatIds),
                'seats_total' => 0, // Tính lại bằng service
                'combo_total' => 0,
                'total_price' => 0,
                'status' => 'pending',
                // Expires theo logic booking (có thể giống hoặc dài hơn lock)
                'expires_at' => Carbon::now()->addMinutes(config('app.seat_lock_timeout', 6)), 
            ]);

            // 3. Sử dụng PricingService để tạo chi tiết và tính tiền
            $seatsTotal = $this->createBookingDetailsWithService($booking, $seatIds, $showtime);

            // 4. Xử lý combos (dùng service)
            $comboTotal = 0;
            if ($request->has('combos')) {
                // Map request combos to format service needs or just calculate
                // Ở đây ta chưa refactor BookingCombos logic vào Service hoàn toàn để create DB records, 
                // nhưng ta dùng Service để tính tiền.
                // Tuy nhiên, logic tạo record booking_combos vẫn cần thiết.
                // Ta vẫn giữ logic cũ nhưng dùng Service check giá nếu cần.
                $comboTotal = $this->addCombosToBooking($booking, $request->combos);
                
                // Validate giá với Service (Optional, để đảm bảo consistency)
                // $serviceComboTotal = $this->pricingService->calculateCombosTotal($request->combos);
            }

            // 5. Cập nhật tổng tiền vào Booking
            // Tái sử dụng method tính tổng của service cho chắc chắn (nếu muốn)
            // $finalTotal = $seatsTotal + $comboTotal;
            
            $booking->update([
                'seats_total' => $seatsTotal,
                'combo_total' => $comboTotal,
                'total_price' => $seatsTotal + $comboTotal,
            ]);

            DB::commit();

            // Load relationships
            $booking->load(['seats', 'combos', 'showtime.movie', 'showtime.room.theater']);

            \Log::info('Booking created successfully', ['booking_id' => $booking->booking_id]);
            
            // Thêm id dự phòng cho frontend
            $booking->id = $booking->booking_id;

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo. Vui lòng thanh toán.',
                'data' => $booking,
                'expires_at' => $booking->expires_at->toISOString(),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking store error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo booking: ' . $e->getMessage()
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
            'data' => $booking
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
                'message' => 'Bạn không có quyền thanh toán booking này'
            ], 403);
        }

        // Kiểm tra trạng thái
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking này đã được xử lý'
            ], 400);
        }

        // Kiểm tra hết hạn
        if ($booking->expires_at && $booking->expires_at->isPast()) {
            $booking->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'message' => 'Booking đã hết hạn'
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
                    'transaction' => $transaction
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Thanh toán thất bại: ' . $e->getMessage()
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
            'transaction'
        ])->findOrFail($id);

        // Kiểm tra booking đã confirmed
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Vé chưa được thanh toán'
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
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $eTicket
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
            'transaction'
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
            ]
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Validate ghế cho actions Hold
     * Ghế phải không bị Booked, và không bị Locked bởi người khác.
     */
    private function validateSeatsForHold($showtimeId, $seatIds, $userId)
    {
        // 1. Check Booking (Confirmed or Pending valid)
        $bookedSeats = BookingDetail::whereHas('booking', function ($query) use ($showtimeId) {
            $query->where('showtime_id', $showtimeId)
                ->where(function ($q) {
                    $q->where('status', 'confirmed')
                        ->orWhere(function ($sq) {
                            $sq->where('status', 'pending')
                                ->where('expires_at', '>', Carbon::now());
                        });
                });
        })->whereIn('seat_id', $seatIds)->exists();

        if ($bookedSeats) {
            throw new \Exception('Một số ghế đã được đặt.');
        }

        // 2. Check Lock (Locked by others)
        // Nếu locked bởi chính userId này -> OK (cho phép gia hạn/giữ lại)
        $lockedByOthers = SeatLock::where('showtime_id', $showtimeId)
            ->whereIn('seat_id', $seatIds)
            ->where('user_id', '!=', $userId) // Khác user
            ->where('expires_at', '>', Carbon::now())
            ->exists();

        if ($lockedByOthers) {
            throw new \Exception('Một số ghế đang được giữ bởi người khác.');
        }
    }

    /**
     * Validate ownership của Locks trước khi Booking
     * User phải đang lock những ghế này.
     */
    private function validateLocksOwnership($showtimeId, $seatIds, $userId)
    {
        // Kiểm tra xem tất cả seat_ids có nằm trong seat_locks của user này cho showtime này không
        $lockedCount = SeatLock::where('showtime_id', $showtimeId)
            ->where('user_id', $userId)
            ->whereIn('seat_id', $seatIds)
            ->where('expires_at', '>', Carbon::now())
            ->count();
        
        // Nếu số lượng lock của user < tổng số ghế yêu cầu -> có ghế chưa lock hoặc hết hạn
        if ($lockedCount < count($seatIds)) {
             throw new \Exception('Bạn chưa giữ ghế hoặc thời gian giữ ghế đã hết. Vui lòng chọn lại.');
        }
    }


    /**
     * Tạo chi tiết đặt vé (BookingDetail) dùng PricingService
     */
    private function createBookingDetailsWithService($booking, $seatIds, $showtime)
    {
        $total = 0;
        $seats = Seat::whereIn('seat_id', $seatIds)->get();

        foreach ($seats as $seat) {
            // Sử dụng PricingService để tính giá
            $ticketPrice = $this->pricingService->calculateTicketPrice($showtime, $seat);

            BookingDetail::create([
                'booking_id' => $booking->booking_id,
                'seat_id' => $seat->seat_id,
                'showtime_id' => $showtime->showtime_id,
                'ticket_code' => $booking->booking_code . '-' . $seat->seat_code,
                'base_price' => $showtime->base_price ?? 100000,
                'seat_extra_price' => $seat->extra_price ?? 0,
                'final_price' => $ticketPrice,
            ]);

            $total += $ticketPrice;
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
        return 'BK' . date('Ymd') . str_pad(
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
        return 'TXN' . date('YmdHis') . rand(1000, 9999);
    }

    /**
     * Generate QR code data
     */
    private function generateQRCode($bookingCode)
    {
        // Trong thực tế, bạn sẽ dùng thư viện QR code
        // Ở đây chỉ return URL để generate QR
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $bookingCode;
    }
}
