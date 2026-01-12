<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\BookingSeat;
use App\Models\SeatLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowtimeController extends Controller
{
    /**
     * GET /api/showtimes
     */
    public function index(Request $request)
    {
        // Ghi log để kiểm tra params từ Web gửi lên
        \Log::info('Showtime Request Params:', $request->all());

        $query = Showtime::with(['movie', 'room.theater.city']);

        // 1. Lọc theo Phim (nếu có)
        if ($request->filled('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        // 2. Lọc theo Thành phố (QUAN TRỌNG: Khớp với giao diện Web bạn đang dùng)
        if ($request->filled('city')) {
            $query->whereHas('room.theater.city', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->city . '%');
            });
        }

        // 3. Lọc theo Rạp cụ thể (nếu có)
        if ($request->filled('theater_id')) {
            $query->whereHas('room', function ($q) use ($request) {
                $q->where('theater_id', $request->theater_id);
            });
        }

        // 4. Lọc theo Ngày
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        } else {
            $query->where('start_time', '>=', Carbon::now());
        }

        $showtimes = $query->orderBy('start_time', 'asc')->get();

        return response()->json([
            'success' => true,
            'count' => $showtimes->count(),
            'data' => $showtimes
        ]);
    }

    /**
     * GET /api/showtimes/{id}/seats
     */
    public function getSeats($id)
    {
        // Tự động giải phóng các ghế đã hết hạn giữ chỗ (6 phút)
        DB::table('bookings')
            ->where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        DB::table('seat_locks')
            ->where('expires_at', '<', Carbon::now())
            ->delete();

        $showtime = Showtime::with(['room.theater', 'movie'])->find($id);

        if (!$showtime) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy suất chiếu'
            ], 404);
        }

        $seats = Seat::where('room_id', $showtime->room_id)
            ->orderBy('row')
            ->orderBy('number')
            ->get();

        // Lấy danh sách ghế đã đặt (confirmed) hoặc đang đợi thanh toán (pending + chưa hết hạn)
        $bookedSeatIds = BookingSeat::where('showtime_id', $id)
            ->whereHas('booking', function ($query) {
                $query->where('status', 'confirmed')
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                            ->where('expires_at', '>', Carbon::now());
                    });
            })->pluck('seat_id')->toArray();

        $lockedSeatIds = SeatLock::where('showtime_id', $id)
            ->where('expires_at', '>', Carbon::now())
            ->pluck('seat_id')->toArray();

        $seats->map(function ($seat) use ($bookedSeatIds, $lockedSeatIds) {
            if (in_array($seat->seat_id, $bookedSeatIds)) {
                $seat->status = 'booked';
            } elseif (in_array($seat->seat_id, $lockedSeatIds)) {
                $seat->status = 'locked';
            } else {
                $seat->status = 'available';
            }
            return $seat;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'showtime' => $showtime,
                'seats' => $seats,
                'seat_map' => $seats->groupBy('row'),
                'summary' => [
                    'total_seats' => $seats->count(),
                    'booked_seats' => count($bookedSeatIds),
                    'locked_seats' => count($lockedSeatIds),
                    'available_seats' => $seats->count() - count($bookedSeatIds) - count($lockedSeatIds)
                ]
            ]
        ]);
    }
}