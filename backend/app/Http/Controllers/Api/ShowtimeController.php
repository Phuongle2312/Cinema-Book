<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\BookingDetail;
use App\Models\SeatLock;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * ShowtimeController
 * Xử lý API liên quan đến lịch chiếu và ghế ngồi
 */
class ShowtimeController extends Controller
{
    /**
     * GET /api/showtimes
     * Lấy danh sách suất chiếu theo phim, rạp hoặc ngày
     */
    public function index(Request $request)
    {
        // Ghi log để debug params
        \Log::info('GET /api/showtimes Params:', $request->all());

        $query = Showtime::with(['movie', 'room.theater.city']);

        // 1. Lọc theo Phim
        if ($request->has('movie_id') && $request->movie_id != null) {
            $query->where('movie_id', $request->movie_id);
        }

        // 2. Lọc theo Rạp
        if ($request->has('theater_id') && $request->theater_id != null) {
            $query->whereHas('room', function ($q) use ($request) {
                $q->where('theater_id', $request->theater_id);
            });
        }

        // 3. Lọc theo Ngày
        if ($request->has('date') && $request->date != null) {
            // Format: YYYY-MM-DD
            $query->whereDate('start_time', $request->date);
        } else {
            // Mặc định: Chỉ lấy các suất chiếu từ thời điểm hiện tại trở đi
            // Nếu bạn đang test dữ liệu cũ thì có thể comment dòng này lại
            // $query->where('start_time', '>=', Carbon::now());
        }

        // Sắp xếp theo thời gian
        $showtimes = $query->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'count' => $showtimes->count(),
            'data' => $showtimes
        ]);
    }

    /**
     * GET /api/showtimes/{id}/seats
     * Lấy sơ đồ ghế ngồi kèm trạng thái (available/booked/locked)
     */
    public function getSeats($id)
    {
        $showtime = Showtime::with(['room', 'movie'])->find($id);

        if (!$showtime) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy suất chiếu'
            ], 404);
        }

        // Lấy tất cả ghế của room
        $seats = Seat::where('room_id', $showtime->room_id)
            ->orderBy('row')
            ->orderBy('number')
            ->get();

        // Lấy danh sách ghế đã đặt (confirmed hoặc pending)
        $bookedSeatIds = BookingDetail::whereHas('booking', function ($query) use ($id) {
            $query->where('showtime_id', $id)
                ->whereIn('status', ['confirmed', 'pending']);
        })->pluck('seat_id')->toArray();

        // Lấy danh sách ghế đang bị lock (chưa hết hạn)
        $lockedSeatIds = SeatLock::where('showtime_id', $id)
            ->where('expires_at', '>', Carbon::now())
            ->pluck('seat_id')->toArray();

        // Gán trạng thái cho từng ghế
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

        // Nhóm ghế theo hàng để dễ hiển thị
        $seatMap = $seats->groupBy('row');

        return response()->json([
            'success' => true,
            'data' => [
                'showtime' => $showtime,
                'seats' => $seats,
                'seat_map' => $seatMap,
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
