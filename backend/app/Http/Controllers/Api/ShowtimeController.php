<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\BookingSeat;
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
            ->with('seatType')
            ->orderBy('row')
            ->orderBy('number')
            ->get();

        // Lấy danh sách ghế đã đặt (confirmed hoặc pending)
        $bookedSeatIds = BookingSeat::whereHas('booking', function ($query) use ($id) {
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
