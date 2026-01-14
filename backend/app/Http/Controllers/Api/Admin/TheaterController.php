<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Theater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Controller: TheaterController
 * Mục đích: Quản lý CRUD rạp chiếu (chỉ admin)
 */
class TheaterController extends Controller
{
    /**
     * Danh sách tất cả rạp
     * GET /api/admin/theaters
     */
    public function index(Request $request)
    {
        $query = Theater::with(['city']);

        // Filter by city
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $theaters = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $theaters,
        ]);
    }

    /**
     * Thêm rạp mới
     * POST /api/admin/theaters
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string',
            'total_rooms' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $theater = Theater::create($request->all());
        $theater->load('city');

        return response()->json([
            'success' => true,
            'message' => 'Rạp chiếu đã được tạo thành công',
            'data' => $theater,
        ], 201);
    }

    /**
     * Cập nhật thông tin rạp
     * PUT /api/admin/theaters/{id}
     */
    public function update(Request $request, $id)
    {
        $theater = Theater::find($id);

        if (! $theater) {
            return response()->json([
                'success' => false,
                'message' => 'Rạp không tồn tại',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'city_id' => 'sometimes|exists:cities,id',
            'address' => 'sometimes|string',
            'total_rooms' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $theater->update($request->all());
        $theater->load('city');

        return response()->json([
            'success' => true,
            'message' => 'Rạp chiếu đã được cập nhật',
            'data' => $theater,
        ]);
    }

    /**
     * Xóa rạp
     * DELETE /api/admin/theaters/{id}
     */
    public function destroy($id)
    {
        $theater = Theater::find($id);

        if (! $theater) {
            return response()->json([
                'success' => false,
                'message' => 'Rạp không tồn tại',
            ], 404);
        }

        // Kiểm tra xem rạp có suất chiếu nào không
        if ($theater->showtimes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa rạp đang có suất chiếu',
            ], 400);
        }

        $theater->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rạp chiếu đã được xóa',
        ]);
    }
}
