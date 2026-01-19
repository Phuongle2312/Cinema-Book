<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Theater;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Controller: ShowtimeController
 * Mục đích: Quản lý suất chiếu và định giá (chỉ admin)
 */
class ShowtimeController extends Controller
{
    /**
     * Danh sách tất cả suất chiếu
     * GET /api/admin/showtimes
     */
    public function index(Request $request)
    {
        $query = Showtime::with(['movie', 'room.theater']);

        // Filter by movie
        if ($request->has('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        // Filter by theater
        if ($request->has('theater_id')) {
            $query->where('theater_id', $request->theater_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }

        $showtimes = $query->orderBy('start_time', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $showtimes,
        ]);
    }

    /**
     * Thêm suất chiếu mới
     * POST /api/admin/showtimes
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,movie_id',
            'theater_id' => 'required|exists:theaters,theater_id',
            'room_id' => 'required|exists:rooms,room_id',
            'show_date' => 'required|date',
            'show_time' => 'required|date_format:H:i',
            'base_price' => 'required|numeric|min:0',
            'gold_price' => 'nullable|numeric|min:0',
            'platinum_price' => 'nullable|numeric|min:0',
            'box_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Kiểm tra room có thuộc theater không
        $room = Room::where('room_id', $request->room_id)
            ->where('theater_id', $request->theater_id)
            ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chiếu không thuộc rạp này',
            ], 400);
        }

        // --- LOGIC KIỂM TRA TRÙNG LỊCH (CONFLICT CHECK) ---
        // 1. Lấy thông tin phim để biết thời lượng
        $movie = Movie::find($request->movie_id);
        if (!$movie) {
            return response()->json(['success' => false, 'message' => 'Phim không tồn tại'], 404);
        }

        $newStart = Carbon::parse($request->show_date . ' ' . $request->show_time);

        $overlap = $this->checkOverlap($request->room_id, $newStart, $movie->duration);
        if ($overlap) {
            return response()->json([
                'success' => false,
                'message' => $overlap,
            ], 400);
        }
        // --- KẾT THÚC KIỂM TRA ---

        DB::beginTransaction();
        try {
            // Prepare data for creation
            $data = $request->all();
            $data['start_time'] = $newStart;
            // Remove helper fields if they interfere (Showtime model fillable doesn't have them, so they are ignored, but good to be safe)
            unset($data['show_date']);
            unset($data['show_time']);

            $showtime = Showtime::create($data);

            // Tự động tạo seats cho showtime này dựa trên room
            // FIX: Không tạo record cứng cho seats. Availability được tính động.

            DB::commit();

            $showtime->load(['movie', 'room.theater', 'room']);

            return response()->json([
                'success' => true,
                'message' => 'Suất chiếu đã được tạo thành công',
                'data' => $showtime,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo suất chiếu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật suất chiếu
     * PUT /api/admin/showtimes/{id}
     */
    public function update(Request $request, $id)
    {
        $showtime = Showtime::find($id);

        if (!$showtime) {
            return response()->json([
                'success' => false,
                'message' => 'Suất chiếu không tồn tại',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'show_date' => 'sometimes|date',
            'show_time' => 'sometimes|date_format:H:i',
            'base_price' => 'sometimes|numeric|min:0',
            'gold_price' => 'nullable|numeric|min:0',
            'platinum_price' => 'nullable|numeric|min:0',
            'box_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check for start_time update needed
        if ($request->has('show_date') || $request->has('show_date') || $request->has('show_time')) {
            $currentStart = $showtime->start_time;
            $newDateString = $request->input('show_date', $currentStart->format('Y-m-d'));
            $newTimeString = $request->input('show_time', $currentStart->format('H:i'));

            $newStart = Carbon::parse("$newDateString $newTimeString");

            // Check Overlap
            $movie = $showtime->movie; // Getting existing movie duration
            if ($request->has('movie_id')) {
                $movie = Movie::find($request->movie_id);
            }

            $duration = $movie ? $movie->duration : 0;
            $roomId = $request->input('room_id', $showtime->room_id);

            $overlap = $this->checkOverlap($roomId, $newStart, $duration, $showtime->showtime_id);
            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => $overlap,
                ], 400);
            }

            $data = $request->all();
            $data['start_time'] = $newStart;
            unset($data['show_date']);
            unset($data['show_time']);

            $showtime->update($data);
        } else {
            // Also check overlap if ONLY room changed (rare but possible) or movie changed
            if ($request->has('room_id') || $request->has('movie_id')) {
                $roomId = $request->input('room_id', $showtime->room_id);
                $movie = $showtime->movie;
                if ($request->has('movie_id'))
                    $movie = Movie::find($request->movie_id);

                $overlap = $this->checkOverlap($roomId, $showtime->start_time, $movie->duration, $showtime->showtime_id);
                if ($overlap) {
                    return response()->json(['success' => false, 'message' => $overlap], 400);
                }
            }

            $showtime->update($request->all());
        }

        $showtime->load(['movie', 'room.theater', 'room']);

        return response()->json([
            'success' => true,
            'message' => 'Suất chiếu đã được cập nhật',
            'data' => $showtime,
        ]);
    }

    /**
     * Check for overlapping showtimes
     * Returns error message string or null if safe.
     */
    private function checkOverlap($roomId, $newStartTime, $duration, $excludeId = null)
    {
        $cleaningTime = 15; // 15 mins
        $newStart = Carbon::parse($newStartTime);
        $newEnd = $newStart->copy()->addMinutes($duration + $cleaningTime);

        $query = Showtime::with('movie')
            ->where('room_id', $roomId)
            ->whereDate('start_time', $newStart->toDateString());

        if ($excludeId) {
            $query->where('showtime_id', '!=', $excludeId);
        }

        $existingShowtimes = $query->get();

        foreach ($existingShowtimes as $existing) {
            $existingStart = Carbon::parse($existing->start_time);
            $existingDuration = $existing->movie ? $existing->movie->duration : 0;
            $existingEnd = $existingStart->copy()->addMinutes($existingDuration + $cleaningTime);

            if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                return "Phòng chiếu bị trùng lịch! Phòng bận từ " . $existingStart->format('H:i') . " đến " . $existingEnd->format('H:i');
            }
        }

        return null;
    }

    /**
     * Xóa suất chiếu
     * DELETE /api/admin/showtimes/{id}
     */
    public function destroy($id)
    {
        $showtime = Showtime::find($id);

        if (!$showtime) {
            return response()->json([
                'success' => false,
                'message' => 'Suất chiếu không tồn tại',
            ], 404);
        }

        // Kiểm tra xem có booking nào không
        if ($showtime->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa suất chiếu đã có người đặt vé',
            ], 400);
        }

        $showtime->delete();

        return response()->json([
            'success' => true,
            'message' => 'Suất chiếu đã được xóa',
        ]);
    }
}
