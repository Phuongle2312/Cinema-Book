<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
        $query = Showtime::with(['movie', 'theater', 'room']);

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
            $query->whereDate('show_date', $request->date);
        }

        $showtimes = $query->orderBy('show_date', 'desc')
                          ->orderBy('show_time', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $showtimes
        ]);
    }

    /**
     * Thêm suất chiếu mới
     * POST /api/admin/showtimes
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'theater_id' => 'required|exists:theaters,id',
            'room_id' => 'required|exists:rooms,id',
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
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra room có thuộc theater không
        $room = Room::where('id', $request->room_id)
                    ->where('theater_id', $request->theater_id)
                    ->first();

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Phòng chiếu không thuộc rạp này'
            ], 400);
        }

        // Kiểm tra xem có suất chiếu trùng giờ không
        $conflictShowtime = Showtime::where('room_id', $request->room_id)
            ->where('show_date', $request->show_date)
            ->where('show_time', $request->show_time)
            ->exists();

        if ($conflictShowtime) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có suất chiếu khác trong phòng này vào thời gian này'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $showtime = Showtime::create($request->all());

            // Tự động tạo seats cho showtime này dựa trên room
            $roomSeats = Seat::where('room_id', $request->room_id)->get();
            
            foreach ($roomSeats as $seat) {
                $showtime->seats()->create([
                    'seat_id' => $seat->id,
                    'is_available' => true,
                ]);
            }

            DB::commit();

            $showtime->load(['movie', 'theater', 'room']);

            return response()->json([
                'success' => true,
                'message' => 'Suất chiếu đã được tạo thành công',
                'data' => $showtime
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo suất chiếu: ' . $e->getMessage()
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
                'message' => 'Suất chiếu không tồn tại'
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
                'errors' => $validator->errors()
            ], 422);
        }

        $showtime->update($request->all());
        $showtime->load(['movie', 'theater', 'room']);

        return response()->json([
            'success' => true,
            'message' => 'Suất chiếu đã được cập nhật',
            'data' => $showtime
        ]);
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
                'message' => 'Suất chiếu không tồn tại'
            ], 404);
        }

        // Kiểm tra xem có booking nào không
        if ($showtime->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa suất chiếu đã có người đặt vé'
            ], 400);
        }

        $showtime->delete();

        return response()->json([
            'success' => true,
            'message' => 'Suất chiếu đã được xóa'
        ]);
    }
}
