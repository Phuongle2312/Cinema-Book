<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\BookingDetail;
use App\Models\SeatLock;
use Illuminate\Http\Request;
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
            // Sửa logic: Lọc qua quan hệ city của theater
            $query->whereHas('room.theater.city', function ($q) use ($request) {
                // Sử dụng LIKE để linh hoạt hơn với dấu tiếng Việt hoặc khoảng trắng
                $q->where('name', 'LIKE', '%' . $request->city . '%');
            });
        }

        // 3. Lọc theo Rạp cụ thể (nếu có)
        if ($request->filled('theater_id')) {
            $query->whereHas('room', function ($q) use ($request) {
                $q->where('theater_id', $request->theater_id);
            });
        }

        // 4. Lọc theo Ngày (Khớp với thanh chọn ngày 8, 9, 10... trên Web)
        if ($request->filled('date')) {
            // Đảm bảo Front-end gửi định dạng YYYY-MM-DD (ví dụ: 2026-01-08)
            $query->whereDate('start_time', $request->date);
        } else {
            // Nếu không chọn ngày, chỉ lấy các suất chiếu sắp tới (bỏ qua suất đã chiếu xong)
            $query->where('start_time', '>=', Carbon::now());
        }

        // Sắp xếp theo thời gian sớm nhất lên trước
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
        $showtime = Showtime::with(['room', 'movie'])->find($id);

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

        $bookedSeatIds = BookingDetail::whereHas('booking', function ($query) use ($id) {
            $query->where('showtime_id', $id)
                ->whereIn('status', ['confirmed', 'pending']);
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